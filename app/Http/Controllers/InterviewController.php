<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobApplicationEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['business', 'professional'], true), 403);

        $interviews = Interview::query()
            ->with(['jobApplication.jobPosting.owner.businessProfile', 'jobApplication.professional'])
            ->when($user->role === 'business', fn ($query) => $query->where('business_user_id', $user->id))
            ->when($user->role === 'professional', fn ($query) => $query->whereHas('jobApplication', fn ($applications) => $applications->where('user_id', $user->id)))
            ->orderBy('scheduled_at')
            ->get();

        return view('interviews.index', [
            'interviews' => $interviews,
            'role' => $user->role,
        ]);
    }

    public function store(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);
        $jobApplication->loadMissing('jobPosting');
        abort_unless(
            $jobApplication->jobPosting !== null
            && (int) $jobApplication->jobPosting->user_id === (int) $request->user()->id,
            403
        );

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60, 90])],
            'mode' => ['required', Rule::in(['in_person', 'video', 'phone'])],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['mode'] !== 'phone' && blank($data['location'] ?? null)) {
            return back()->withErrors(['location' => 'Indica la sede oppure il link del colloquio.'])->withInput();
        }

        $interview = $jobApplication->interviews()->create([
            ...$data,
            'business_user_id' => $request->user()->id,
            'status' => 'scheduled',
        ]);

        $previousStatus = $jobApplication->status;
        $jobApplication->update(['status' => JobApplication::STATUS_INTERVIEW_SCHEDULED]);

        JobApplicationEvent::create([
            'job_application_id' => $jobApplication->id,
            'actor_user_id' => $request->user()->id,
            'type' => 'interview_scheduled',
            'label' => 'Colloquio programmato per '.$interview->scheduled_at->format('d/m/Y H:i'),
            'from_status' => $previousStatus,
            'to_status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
            'metadata' => [
                'interview_id' => $interview->id,
                'mode' => $interview->mode,
                'duration_minutes' => $interview->duration_minutes,
            ],
        ]);

        return back()->with('status', 'Colloquio programmato.')->with('status_variant', 'success');
    }
}
