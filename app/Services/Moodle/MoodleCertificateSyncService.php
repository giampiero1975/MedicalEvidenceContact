<?php

namespace App\Services\Moodle;

use App\Models\MoodleUserLink;
use App\Models\UserCertificate;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

class MoodleCertificateSyncService
{
    public function __construct(
        private readonly MoodleFlowLogger $logger
    ) {
    }

    /**
     * @return array{received:int,saved:int,driver:string,trace_id:string}
     */
    public function sync(MoodleUserLink $link): array
    {
        $flow = 'certificate_sync';
        $startedAt = microtime(true);
        $traceId = $this->logger->begin($flow, [
            'moodle_user_link_id' => $link->id,
            'laravel_user_id' => $link->laravel_user_id,
            'moodle_site_id' => $link->moodle_site_id,
            'moodle_user_id' => $link->moodle_user_id,
            'link_status' => $link->status,
        ]);

        try {
            abort_unless($link->status === 'active', 422, 'Il collegamento Moodle non è attivo.');

            $link->loadMissing('moodleSite');
            $driver = $link->moodleSite->certificate_sync_driver ?: 'disabled';

            $this->logger->step($traceId, $flow, 'driver.resolved', [
                'driver' => $driver,
                'site_name' => $link->moodleSite->name,
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            $client = MoodleApiClient::forUserLink($link);

            $this->logger->step($traceId, $flow, 'moodle_api.sync.started', [
                'driver' => $driver,
            ]);

            $payload = match ($driver) {
                'local_plugin', 'local_laravelcertsync' => $client->getUserCertificatesViaLocalPlugin($link),
                'customcert', 'mod_customcert', 'native_mod_customcert' => $client->getCustomcertIssuesForLinkedUser($link),
                default => throw new RuntimeException("Driver sincronizzazione attestati non supportato: {$driver}"),
            };

            $this->logger->step($traceId, $flow, 'moodle_api.sync.completed', [
                'driver' => $driver,
                'payload_top_level_keys' => array_keys($payload),
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            $items = $this->extractItems($payload);
            $saved = 0;
            $skipped = 0;

            $this->logger->step($traceId, $flow, 'certificates.extracted', [
                'received' => $items->count(),
            ]);

            foreach ($items as $index => $item) {
                if ($this->persist($link, $item)) {
                    $saved++;
                    continue;
                }

                $skipped++;
                $this->logger->warning($traceId, $flow, 'certificate.skipped', [
                    'index' => $index,
                    'available_keys' => array_keys($item),
                    'reason' => 'missing_issue_id_and_certificate_code',
                ]);
            }

            $now = now();
            $link->forceFill(['last_certificate_sync_at' => $now])->save();
            $link->moodleSite->forceFill(['last_certificate_sync_at' => $now])->save();

            $result = [
                'received' => $items->count(),
                'saved' => $saved,
                'driver' => $driver,
                'trace_id' => $traceId,
            ];

            $this->logger->success($traceId, $flow, [
                ...$result,
                'skipped' => $skipped,
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->logger->failure($traceId, $flow, 'flow.failed', $exception, [
                'elapsed_ms' => $this->elapsedMs($startedAt),
            ]);

            throw $exception;
        }
    }

    /** @return Collection<int, array<string, mixed>> */
    private function extractItems(array $payload): Collection
    {
        $items = $payload['certificates']
            ?? $payload['issues']
            ?? $payload['data']
            ?? $payload;

        if (! is_array($items)) {
            return collect();
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->values();
    }

    /** @param array<string, mixed> $item */
    private function persist(MoodleUserLink $link, array $item): bool
    {
        $issueId = $this->int($item, ['issue.id', 'issueid', 'issue_id', 'id']);
        $certificateCode = $this->string($item, ['issue.code', 'code', 'certificatecode', 'certificate_code']);

        if (! $issueId && ! $certificateCode) {
            return false;
        }

        $identity = [
            'laravel_user_id' => $link->laravel_user_id,
            'moodle_site_id' => $link->moodle_site_id,
            'moodle_user_id' => $link->moodle_user_id,
        ];

        $query = UserCertificate::query()->where($identity);
        $issueId
            ? $query->where('moodle_customcert_issue_id', $issueId)
            : $query->where('certificate_code', $certificateCode);

        $certificate = $query->first() ?? new UserCertificate($identity);

        $certificate->fill([
            'moodle_customcert_id' => $this->int($item, ['issue.customcertid', 'customcertid', 'customcert_id', 'certificateid']),
            'moodle_customcert_issue_id' => $issueId,
            'moodle_course_module_id' => $this->int($item, ['coursemoduleid', 'course_module_id', 'cmid']),
            'moodle_context_id' => $this->int($item, ['template.contextid', 'contextid', 'context_id']),
            'course_id' => $this->int($item, ['course.id', 'courseid', 'course_id']),
            'course_fullname' => $this->string($item, ['course.fullname', 'coursefullname', 'course_fullname', 'fullname']),
            'course_shortname' => $this->string($item, ['course.shortname', 'courseshortname', 'course_shortname', 'shortname']),
            'certificate_name' => $this->string($item, ['certificate.name', 'certificatename', 'certificate_name', 'template.name', 'name']),
            'template_id' => $this->int($item, ['template.id', 'templateid', 'template_id']),
            'template_name' => $this->string($item, ['template.name', 'templatename', 'template_name']),
            'certificate_code' => $certificateCode,
            'issued_at' => $this->date($item, ['issue.timecreated', 'timecreated', 'issuedat', 'issued_at', 'issueddate']),
            'expires_at' => $this->date($item, ['issue.expiresat', 'expiresat', 'expires_at', 'expirydate']),
            'download_url' => $this->string($item, ['pdf.url', 'downloadurl', 'download_url']),
            'verification_url' => $this->string($item, ['verificationurl', 'verification_url', 'verifyurl']),
            'verification_is_public' => (bool) ($item['verification_is_public'] ?? $item['verificationispublic'] ?? false),
            'raw_payload_json' => $item,
        ]);

        $certificate->save();

        return true;
    }

    /** @param array<string, mixed> $item */
    private function string(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($item, $key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $item */
    private function int(array $item, array $keys): ?int
    {
        $value = $this->string($item, $keys);
        return is_numeric($value) ? (int) $value : null;
    }

    /** @param array<string, mixed> $item */
    private function date(array $item, array $keys): ?Carbon
    {
        $value = $this->string($item, $keys);

        if ($value === null) {
            return null;
        }

        try {
            return is_numeric($value)
                ? Carbon::createFromTimestamp((int) $value)
                : Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function elapsedMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
