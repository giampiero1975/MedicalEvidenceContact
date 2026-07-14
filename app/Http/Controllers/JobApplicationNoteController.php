<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobApplicationNoteController extends Controller
{
    public function store(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);
        $jobApplication->loadMissing('jobPosting');
        abort_unless($jobApplication->jobPosting !== null && (int) $jobApplication->jobPosting->user_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $jobApplication->notes()->create([
            'author_user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $jobApplication->events()->create([
            'actor_user_id' => $request->user()->id,
            'type' => 'note_added',
            'label' => 'Nota interna aggiunta',
        ]);

        return back()->with('status', 'Nota interna aggiunta.')->with('status_variant', 'success');
    }
}
