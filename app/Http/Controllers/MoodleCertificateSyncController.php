<?php

namespace App\Http\Controllers;

use App\Models\MoodleUserLink;
use App\Services\Moodle\MoodleApiException;
use App\Services\Moodle\MoodleCertificateSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class MoodleCertificateSyncController extends Controller
{
    public function __invoke(
        Request $request,
        MoodleUserLink $moodleUserLink,
        MoodleCertificateSyncService $sync
    ): RedirectResponse {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless($moodleUserLink->laravel_user_id === $request->user()->id, 403);

        try {
            $result = $sync->sync($moodleUserLink);
        } catch (MoodleApiException|Throwable $exception) {
            report($exception);

            return redirect()
                ->route('professional.moodle.index')
                ->with('status', 'Sincronizzazione attestati non riuscita. Controlla il log Moodle o riprova più tardi.')
                ->with('status_variant', 'danger');
        }

        return redirect()
            ->route('professional.moodle.index')
            ->with('status', "Sincronizzazione completata: {$result['saved']} attestati aggiornati su {$result['received']} ricevuti.")
            ->with('status_variant', 'success');
    }
}
