<?php

namespace App\Http\Controllers;

use App\Mail\TransactionalActionMail;
use App\Models\BusinessPointOfContact;
use App\Models\User;
use App\Services\TransactionalNotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessPointOfContactController extends Controller
{
    public function index(Request $request): View
    {
        $businessProfile = $this->businessProfileFor($request);
        $businessProfile->load('pointsOfContact.user');

        return view('business-points-of-contact.index', [
            'businessProfile' => $businessProfile,
            'pointsOfContact' => $businessProfile->pointsOfContact,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $businessProfile = $this->businessProfileFor($request);
        $data = $this->validatedData($request);
        $temporaryPassword = Str::password(16);

        $pointOfContact = DB::transaction(function () use ($businessProfile, $data, $temporaryPassword) {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'business',
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
            ]);

            return $businessProfile->addPointOfContact([
                ...$data,
                'user_id' => $user->id,
            ]);
        });

        $pocUser = $pointOfContact->user;

        app(TransactionalNotificationDispatcher::class)->dispatch(
            $pocUser,
            'account',
            new TransactionalActionMail(
                mailSubject: 'Accesso Medical Evidence Contact per '.$businessProfile->company_name,
                heading: 'Sei stato aggiunto come Point of Contact',
                intro: 'Usa queste credenziali per accedere alla piattaforma. Al primo accesso completa anche la verifica dell’indirizzo email.',
                actionLabel: 'Accedi alla piattaforma',
                actionUrl: route('login'),
                details: [
                    'Azienda: '.$businessProfile->company_name,
                    'Email: '.$pocUser->email,
                    'Password temporanea: '.$temporaryPassword,
                    'Ruolo: '.$pointOfContact->role,
                ],
            )
        );

        $pocUser->sendEmailVerificationNotification();

        return redirect()
            ->route('business-points-of-contact.index')
            ->with('status', 'Point of Contact aggiunto. Le credenziali di accesso sono state inviate via email.');
    }

    public function update(Request $request, BusinessPointOfContact $pointOfContact): RedirectResponse
    {
        $businessProfile = $this->businessProfileFor($request);
        $this->authorizePointOfContact($businessProfile->id, $pointOfContact);

        $data = $this->validatedData($request, $pointOfContact->user_id);
        $emailChanged = $pointOfContact->email !== $data['email'];

        DB::transaction(function () use ($pointOfContact, $data, $emailChanged): void {
            $pointOfContact->update($data);

            if ($pointOfContact->user) {
                $pointOfContact->user->forceFill([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'],
                    'email_verified_at' => $emailChanged ? null : $pointOfContact->user->email_verified_at,
                ])->save();
            }
        });

        if ($emailChanged && $pointOfContact->user) {
            $pointOfContact->user->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('business-points-of-contact.index')
            ->with('status', 'Point of Contact aggiornato.');
    }

    public function destroy(Request $request, BusinessPointOfContact $pointOfContact): RedirectResponse
    {
        $businessProfile = $this->businessProfileFor($request);
        $this->authorizePointOfContact($businessProfile->id, $pointOfContact);

        if ($businessProfile->pointsOfContact()->count() <= 1) {
            return redirect()
                ->route('business-points-of-contact.index')
                ->with('warning', 'Non puoi eliminare l’unico Point of Contact della struttura.');
        }

        DB::transaction(function () use ($businessProfile, $pointOfContact): void {
            $wasPrimary = $pointOfContact->is_primary;
            $linkedUser = $pointOfContact->user;

            $pointOfContact->delete();

            if ($linkedUser && $linkedUser->id !== $businessProfile->user_id) {
                $linkedUser->delete();
            }

            if ($wasPrimary) {
                $replacement = $businessProfile->pointsOfContact()->oldest('id')->first();
                $replacement?->update(['is_primary' => true]);
            }
        });

        return redirect()
            ->route('business-points-of-contact.index')
            ->with('status', 'Point of Contact eliminato.');
    }

    public function designatePrimary(Request $request, BusinessPointOfContact $pointOfContact): RedirectResponse
    {
        $businessProfile = $this->businessProfileFor($request);
        $this->authorizePointOfContact($businessProfile->id, $pointOfContact);

        DB::transaction(function () use ($businessProfile, $pointOfContact): void {
            $businessProfile->pointsOfContact()->update(['is_primary' => false]);
            $pointOfContact->update(['is_primary' => true]);
        });

        return redirect()
            ->route('business-points-of-contact.index')
            ->with('status', 'Point of Contact principale aggiornato.');
    }

    private function validatedData(Request $request, ?int $ignoreUserId = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreUserId)],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', 'string', 'max:150'],
        ]);
    }

    private function businessProfileFor(Request $request)
    {
        abort_unless($request->user()->role === 'business', 403);

        $businessProfile = $request->user()->businessContextProfile();
        abort_unless($businessProfile, 404);

        return $businessProfile;
    }

    private function authorizePointOfContact(int $businessProfileId, BusinessPointOfContact $pointOfContact): void
    {
        abort_unless($pointOfContact->business_profile_id === $businessProfileId, 403);
    }
}
