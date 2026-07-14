<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    public function store(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless(
            $jobPosting->status === 'active' && $jobPosting->expires_at->toDateString() >= now()->toDateString(),
            403
        );

        JobApplication::firstOrCreate([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $request->user()->id,
        ], [
            'status' => JobApplication::STATUS_RECEIVED,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Candidatura inviata. Annuncio aggiunto alla tua lista.');
    }

    public function updateStatus(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);
        abort_unless($jobApplication->jobPosting?->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(JobApplication::statusOptions()))],
        ]);

        $jobApplication->update($data);

        return back()
            ->with('status', 'Stato della candidatura aggiornato.')
            ->with('status_variant', 'success');
    }
}
