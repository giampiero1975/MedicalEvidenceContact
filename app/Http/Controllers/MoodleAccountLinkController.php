<?php

namespace App\Http\Controllers;

use App\Models\MoodleLinkAttempt;
use App\Models\MoodleSite;
use App\Models\MoodleUserLink;
use App\Services\Mail\MoodleSiteMailer;
use App\Services\Moodle\MoodleApiClient;
use App\Services\Moodle\MoodleApiException;
use App\Services\Moodle\MoodleFlowLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MoodleAccountLinkController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'professional', 403);

        return view('professional.moodle.index', [
            'moodleSites' => MoodleSite::query()
                ->where('enabled', true)
                ->orderBy('name')
                ->get(),
            'moodleUserLinks' => $request->user()
                ->moodleUserLinks()
                ->with('moodleSite')
                ->latest()
                ->get(),
        ]);
    }

    public function start(Request $request, MoodleFlowLogger $flowLogger): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);

        $startedAt = microtime(true);

        $data = $request->validate([
            'moodle_site_id' => ['required', 'integer', Rule::exists('moodle_sites', 'id')->where('enabled', true)],
            'lookup_type' => ['required', Rule::in(['email', 'username'])],
            'lookup_value' => ['required', 'string', 'max:255'],
        ]);

        $moodleSite = MoodleSite::query()->whereKey($data['moodle_site_id'])->firstOrFail();
        $lookupValue = trim((string) $data['lookup_value']);
        $lookupHash = hash('sha256', mb_strtolower($lookupValue));
        $maskedLookup = $this->maskLookupValue($data['lookup_type'], $lookupValue);

        $traceId = $flowLogger->begin('account_link.start', [
            'laravel_user_id' => $request->user()->id,
            'moodle_site_id' => $moodleSite->id,
            'moodle_site_name' => $moodleSite->name,
            'lookup_type' => $data['lookup_type'],
            'lookup_masked' => $maskedLookup,
            'ip_address' => $request->ip(),
        ]);

        if ($request->user()->moodleUserLinks()
            ->where('moodle_site_id', $moodleSite->id)
            ->where('status', 'active')
            ->exists()) {
            $flowLogger->warning($traceId, 'account_link.start', 'active_link.already_exists', [
                'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
            ]);

            return redirect()
                ->route('professional.moodle.index')
                ->with('status', 'Hai gia un collegamento attivo per questo sito Moodle.')
                ->with('status_variant', 'info');
        }

        $attempt = MoodleLinkAttempt::create([
            'laravel_user_id' => $request->user()->id,
            'moodle_site_id' => $moodleSite->id,
            'lookup_type' => $data['lookup_type'],
            'lookup_value_hash' => $lookupHash,
            'lookup_value_masked' => $maskedLookup,
            'status' => 'created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $flowLogger->step($traceId, 'account_link.start', 'attempt.created', [
            'attempt_id' => $attempt->id,
            'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
        ]);

        try {
            $flowLogger->step($traceId, 'account_link.start', 'moodle_api.lookup.started', [
                'attempt_id' => $attempt->id,
            ]);

            $users = (new MoodleApiClient($moodleSite))->getUsersByField($data['lookup_type'], $lookupValue);

            $flowLogger->step($traceId, 'account_link.start', 'moodle_api.lookup.completed', [
                'attempt_id' => $attempt->id,
                'users_found' => count($users),
                'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
            ]);
        } catch (MoodleApiException $exception) {
            $flowLogger->failure($traceId, 'account_link.start', 'moodle_api.lookup.failed', $exception, [
                'attempt_id' => $attempt->id,
                'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
            ]);
            report($exception);
            $attempt->update(['status' => 'failed']);

            return $this->genericStartRedirect();
        }

        if (count($users) !== 1 || blank($users[0]['id'] ?? null) || blank($users[0]['email'] ?? null)) {
            $flowLogger->warning($traceId, 'account_link.start', 'moodle_api.lookup.invalid_result', [
                'attempt_id' => $attempt->id,
                'users_found' => count($users),
                'first_user_has_id' => filled($users[0]['id'] ?? null),
                'first_user_has_email' => filled($users[0]['email'] ?? null),
                'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
            ]);
            $attempt->update(['status' => 'failed']);

            return $this->genericStartRedirect();
        }

        $moodleUser = $users[0];
        $maskedEmail = $this->maskEmail((string) $moodleUser['email']);

        $flowLogger->step($traceId, 'account_link.start', 'moodle_user.resolved', [
            'attempt_id' => $attempt->id,
            'moodle_user_id' => $moodleUser['id'],
            'moodle_email_masked' => $maskedEmail,
        ]);

        if (MoodleUserLink::query()
            ->where('moodle_site_id', $moodleSite->id)
            ->where('moodle_user_id', $moodleUser['id'])
            ->where('status', 'active')
            ->exists()) {
            $flowLogger->warning($traceId, 'account_link.start', 'moodle_user.already_linked', [
                'attempt_id' => $attempt->id,
                'moodle_user_id' => $moodleUser['id'],
                'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
            ]);

            $attempt->update([
                'moodle_user_id' => $moodleUser['id'],
                'moodle_email_masked' => $maskedEmail,
                'status' => 'failed',
            ]);

            return redirect()
                ->route('professional.moodle.index')
                ->with('status', 'Non e stato possibile completare il collegamento automaticamente. Contatta l assistenza.')
                ->with('status_variant', 'danger');
        }

        $code = (string) random_int(100000, 999999);

        try {
            $flowLogger->step($traceId, 'account_link.start', 'verification.prepare.started', [
                'attempt_id' => $attempt->id,
                'recipient_masked' => $maskedEmail,
            ]);

            DB::transaction(function () use ($attempt, $moodleUser, $code, $moodleSite, $flowLogger, $traceId, $maskedEmail): void {
                MoodleLinkAttempt::query()
                    ->where('laravel_user_id', $attempt->laravel_user_id)
                    ->where('moodle_site_id', $attempt->moodle_site_id)
                    ->where('id', '!=', $attempt->id)
                    ->whereIn('status', ['created', 'sent'])
                    ->update(['status' => 'cancelled', 'consumed_at' => now()]);

                $attempt->update([
                    'moodle_user_id' => $moodleUser['id'],
                    'moodle_email_masked' => $maskedEmail,
                    'verification_code_hash' => Hash::make($code),
                    'expires_at' => now()->addMinutes(15),
                    'status' => 'sent',
                ]);

                $flowLogger->step($traceId, 'account_link.start', 'verification.attempt_marked_sent', [
                    'attempt_id' => $attempt->id,
                    'expires_at' => $attempt->expires_at?->toISOString(),
                ]);

                app(MoodleSiteMailer::class)->sendMoodleAccountLinkCode((string) $moodleUser['email'], $code, $moodleSite);

                $flowLogger->step($traceId, 'account_link.start', 'verification.email.sent', [
                    'attempt_id' => $attempt->id,
                    'recipient_masked' => $maskedEmail,
                ]);
            });
        } catch (Throwable $exception) {
            $flowLogger->failure($traceId, 'account_link.start', 'verification.prepare.failed', $exception, [
                'attempt_id' => $attempt->id,
                'recipient_masked' => $maskedEmail,
                'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
            ]);
            report($exception);
            $attempt->update(['status' => 'failed']);

            return $this->genericStartRedirect();
        }

        $flowLogger->success($traceId, 'account_link.start', [
            'attempt_id' => $attempt->id,
            'redirect_route' => 'professional.moodle.verify.show',
            'elapsed_ms' => $this->elapsedMilliseconds($startedAt),
        ]);

        return redirect()
            ->route('professional.moodle.verify.show', $attempt)
            ->with('status', 'Se i dati corrispondono a un account Moodle, riceverai un codice all email associata.')
            ->with('status_variant', 'info');
    }

    public function showVerify(Request $request, MoodleLinkAttempt $attempt): View
    {
        $this->authorizeAttemptOwner($request, $attempt);

        return view('professional.moodle.verify', [
            'attempt' => $attempt->load('moodleSite'),
        ]);
    }

    public function verify(Request $request, MoodleLinkAttempt $attempt): RedirectResponse
    {
        $this->authorizeAttemptOwner($request, $attempt);

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if ($attempt->status !== 'sent' || $attempt->consumed_at) {
            return back()->withErrors(['code' => 'Codice non valido o gia utilizzato.']);
        }

        if ($attempt->expires_at?->isPast()) {
            $attempt->update(['status' => 'expired']);

            return back()->withErrors(['code' => 'Codice scaduto. Richiedi un nuovo collegamento.']);
        }

        if ($attempt->attempts_count >= 5) {
            $attempt->update(['status' => 'failed']);

            return back()->withErrors(['code' => 'Numero massimo di tentativi raggiunto.']);
        }

        $attempt->increment('attempts_count');
        $attempt->refresh();

        if (! Hash::check($data['code'], (string) $attempt->verification_code_hash)) {
            return back()->withErrors(['code' => 'Codice non valido.']);
        }

        try {
            $moodleUsers = (new MoodleApiClient($attempt->moodleSite))->getUserById((int) $attempt->moodle_user_id);
            $moodleUser = $moodleUsers[0] ?? [];
        } catch (MoodleApiException $exception) {
            report($exception);

            return back()->withErrors(['code' => 'Non e stato possibile verificare lo snapshot Moodle. Riprova piu tardi.']);
        }

        try {
            DB::transaction(function () use ($attempt, $moodleUser): void {
                MoodleUserLink::create([
                    'laravel_user_id' => $attempt->laravel_user_id,
                    'moodle_site_id' => $attempt->moodle_site_id,
                    'moodle_user_id' => $attempt->moodle_user_id,
                    'moodle_idnumber' => $moodleUser['idnumber'] ?? null,
                    'moodle_username' => $moodleUser['username'] ?? null,
                    'moodle_email' => $moodleUser['email'] ?? null,
                    'linked_via' => 'email_code',
                    'linked_at' => now(),
                    'last_verified_at' => now(),
                    'status' => 'active',
                ]);

                $attempt->update([
                    'status' => 'verified',
                    'consumed_at' => now(),
                ]);
            });
        } catch (QueryException $exception) {
            report($exception);
            $attempt->update(['status' => 'failed']);

            return redirect()
                ->route('professional.moodle.index')
                ->with('status', 'Non e stato possibile completare il collegamento automaticamente. Contatta l assistenza.')
                ->with('status_variant', 'danger');
        }

        return redirect()
            ->route('professional.moodle.index')
            ->with('status', 'Account Moodle collegato.')
            ->with('status_variant', 'success');
    }

    public function cancel(Request $request, MoodleLinkAttempt $attempt): RedirectResponse
    {
        $this->authorizeAttemptOwner($request, $attempt);

        if (in_array($attempt->status, ['created', 'sent'], true)) {
            $attempt->update([
                'status' => 'cancelled',
                'consumed_at' => now(),
            ]);
        }

        return redirect()
            ->route('professional.moodle.index')
            ->with('status', 'Collegamento Moodle annullato.')
            ->with('status_variant', 'info');
    }

    private function authorizeAttemptOwner(Request $request, MoodleLinkAttempt $attempt): void
    {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless($attempt->laravel_user_id === $request->user()->id, 403);
    }

    private function genericStartRedirect(): RedirectResponse
    {
        return redirect()
            ->route('professional.moodle.index')
            ->with('status', 'Non e stato possibile completare il collegamento. Verifica i dati inseriti o riprova piu tardi.')
            ->with('status_variant', 'danger');
    }

    private function maskLookupValue(string $lookupType, string $value): string
    {
        return $lookupType === 'email' ? $this->maskEmail($value) : mb_substr($value, 0, 2).'***';
    }

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1).'***@'.$domain;
    }

    private function elapsedMilliseconds(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
