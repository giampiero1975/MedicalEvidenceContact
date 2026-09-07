<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobPostingFavoriteController extends Controller
{
    public function store(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless(
            $jobPosting->status === 'active' && $jobPosting->expires_at->toDateString() >= now()->toDateString(),
            403
        );

        $request->user()->favoriteJobPostings()->syncWithoutDetaching([$jobPosting->id]);

        return back()->with('status', 'Annuncio salvato nei preferiti.');
    }

    public function destroy(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);

        $request->user()->favoriteJobPostings()->detach($jobPosting->id);

        return back()->with('status', 'Annuncio rimosso dai preferiti.');
    }
}
