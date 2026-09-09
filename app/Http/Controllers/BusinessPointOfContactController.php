<?php

namespace App\Http\Controllers;

use App\Mail\TransactionalActionMail;
use App\Models\User;
use App\Services\TransactionalNotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessPointOfContactController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'business', 403);

        $businessProfile = $request->user()
            ->businessProfile()
            ->with('pointsOfContact.user')
            ->firstOrFail();

        return view('business-points-of-contact.index', [
            'businessProfile' => $businessProfile,
            'pointsOfContact' => $businessProfile->pointsOfContact,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', 'string', 'max:150'],
        ]);

        $businessProfile = $request->user()->businessProfile()->firstOrFail();
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
}
