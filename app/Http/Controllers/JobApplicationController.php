<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'status' => 'inviata',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Candidatura inviata. Annuncio aggiunto alla tua lista.');
    }
}
