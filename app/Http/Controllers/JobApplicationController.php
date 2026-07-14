<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class JobApplicationController extends Controller
{
    public function store(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless(
            $jobPosting->status === 'active' && $jobPosting->expires_at->toDateString() >= now()->toDateString(),
            403
        );

        [$application, $created] = [null, false];
        DB::transaction(function () use ($request, $jobPosting, &$application, &$created): void {
            $application = JobApplication::firstOrCreate([
                'job_posting_id' => $jobPosting->id,
                'user_id' => $request->user()->id,
            ], [
                'status' => JobApplication::STATUS_RECEIVED,
            ]);
            $created = $application->wasRecentlyCreated;

            if ($created) {
                $application->events()->create([
                    'actor_user_id' => $request->user()->id,
                    'type' => 'application_created',
                    'label' => 'Candidatura inviata',
                    'to_status' => JobApplication::STATUS_RECEIVED,
                ]);
            }
        });

        return redirect()->route('dashboard')->with('status', 'Candidatura inviata. Annuncio aggiunto alla tua lista.');
    }

    public function updateStatus(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);
        $jobApplication->loadMissing('jobPosting');
        abort_unless($jobApplication->jobPosting !== null && (int) $jobApplication->jobPosting->user_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(JobApplication::statusOptions()))],
        ]);

        $oldStatus = $jobApplication->status;

        DB::transaction(function () use ($jobApplication, $request, $data, $oldStatus): void {
            $updatedRows = JobApplication::query()->whereKey($jobApplication->getKey())->update(['status' => $data['status']]);
            if ($updatedRows !== 1) {
                throw new RuntimeException('Impossibile aggiornare lo stato della candidatura.');
            }

            if ($oldStatus !== $data['status']) {
                $jobApplication->events()->create([
                    'actor_user_id' => $request->user()->id,
                    'type' => 'status_changed',
                    'label' => 'Stato aggiornato a '.(JobApplication::statusOptions()[$data['status']] ?? $data['status']),
                    'from_status' => $oldStatus,
                    'to_status' => $data['status'],
                ]);
            }
        });

        return back()->with('status', 'Stato della candidatura aggiornato.')->with('status_variant', 'success');
    }
}
