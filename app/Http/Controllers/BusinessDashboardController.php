<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->role === 'business', 403);

        $ownedPostingIds = JobPosting::query()
            ->where('user_id', $user->id)
            ->select('id');

        $applicationsQuery = JobApplication::query()
            ->whereIn('job_posting_id', $ownedPostingIds);

        $pipeline = collect(JobApplication::statusOptions())
            ->mapWithKeys(fn (string $label, string $status) => [
                $status => [
                    'label' => $label,
                    'count' => (clone $applicationsQuery)->where('status', $status)->count(),
                ],
            ]);

        $recentApplications = (clone $applicationsQuery)
            ->with([
                'professional:id,name,first_name,last_name,profile_photo_path',
                'jobPosting:id,title',
            ])
            ->latest()
            ->limit(6)
            ->get();

        $upcomingInterviews = Interview::query()
            ->where('business_user_id', $user->id)
            ->whereIn('status', ['scheduled', 'accepted'])
            ->where('scheduled_at', '>=', now())
            ->with([
                'jobApplication.professional:id,name,first_name,last_name',
                'jobApplication.jobPosting:id,title',
            ])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $activePostings = JobPosting::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', today())
            ->count();

        $totalApplications = (clone $applicationsQuery)->count();
        $scheduledInterviews = Interview::query()
            ->where('business_user_id', $user->id)
            ->whereIn('status', ['scheduled', 'accepted'])
            ->where('scheduled_at', '>=', now())
            ->count();
        $hiredCandidates = (clone $applicationsQuery)
            ->where('status', JobApplication::STATUS_HIRED)
            ->count();

        $alerts = [
            'stale_applications' => (clone $applicationsQuery)
                ->whereIn('status', [JobApplication::STATUS_RECEIVED, JobApplication::STATUS_REVIEW])
                ->where('created_at', '<=', now()->subDays(7))
                ->count(),
            'interviews_today' => Interview::query()
                ->where('business_user_id', $user->id)
                ->whereIn('status', ['scheduled', 'accepted'])
                ->whereDate('scheduled_at', today())
                ->count(),
            'expiring_postings' => JobPosting::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->whereBetween('expires_at', [today(), today()->addDays(7)])
                ->count(),
        ];

        return view('business.dashboard', [
            'metrics' => [
                'active_postings' => $activePostings,
                'applications' => $totalApplications,
                'interviews' => $scheduledInterviews,
                'hired' => $hiredCandidates,
            ],
            'pipeline' => $pipeline,
            'recentApplications' => $recentApplications,
            'upcomingInterviews' => $upcomingInterviews,
            'alerts' => $alerts,
        ]);
    }
}
