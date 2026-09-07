<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\MoodleSite;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->role === 'professional', 403);

        $user = $request->user();

        $jobApplications = $user
            ->jobApplications()
            ->with('jobPosting')
            ->latest()
            ->get();

        $activeApplicationStatuses = [
            JobApplication::STATUS_RECEIVED,
            JobApplication::STATUS_REVIEW,
            JobApplication::STATUS_INTERVIEW_SCHEDULED,
            JobApplication::STATUS_INTERVIEW_COMPLETED,
            JobApplication::STATUS_SUITABLE,
        ];

        $positiveApplicationStatuses = [
            JobApplication::STATUS_SUITABLE,
            JobApplication::STATUS_HIRED,
        ];

        $moodleSites = MoodleSite::query()
            ->where('enabled', true)
            ->orderBy('name')
            ->get();

        $moodleUserLinks = $user
            ->moodleUserLinks()
            ->with('moodleSite')
            ->latest()
            ->get();

        $certificates = $user
            ->certificates()
            ->latest('issued_at')
            ->get();

        $latestCertificate = $certificates->first();
        $completedCoursesCount = $certificates
            ->pluck('course_id')
            ->filter()
            ->unique()
            ->count();

        $profileItems = $user
            ->professionalProfileItems()
            ->latest()
            ->get();

        $profileFields = [
            $user->first_name,
            $user->last_name,
            $user->phone,
            $user->nationality,
            $user->address_city,
            $user->address_country,
            $user->address_province,
            $user->postal_code,
            $user->street_address,
        ];

        $isItalian = in_array(
            strtolower(trim((string) $user->nationality)),
            ['italiana', 'italiano', 'italia', 'italian'],
            true
        );

        $documents = [
            [
                'label' => 'Attestato ATA',
                'uploaded' => filled($user->ata_certificate_path),
                'required' => true,
            ],
        ];

        if (! $isItalian) {
            $documents[] = [
                'label' => 'Permesso di soggiorno',
                'uploaded' => filled($user->residence_permit_path),
                'required' => true,
            ];
        }

        $completionChecks = [
            ...collect($profileFields)->map(fn ($value) => filled($value))->all(),
            $profileItems->isNotEmpty(),
            ...collect($documents)
                ->filter(fn ($document) => $document['required'])
                ->map(fn ($document) => $document['uploaded'])
                ->all(),
        ];

        $profileCompletion = (int) round(
            (collect($completionChecks)->filter()->count() / count($completionChecks)) * 100
        );

        return view('professionals.dashboard-overview', [
            'jobApplications' => $jobApplications,
            'activeApplicationsCount' => $jobApplications->whereIn('status', $activeApplicationStatuses)->count(),
            'acceptedApplicationsCount' => $jobApplications->whereIn('status', $positiveApplicationStatuses)->count(),
            'availableJobsCount' => JobPosting::query()->visibleToProfessionals()->count(),
            'profileCompletion' => $profileCompletion,
            'profileItems' => $profileItems,
            'moodleSites' => $moodleSites,
            'moodleUserLinks' => $moodleUserLinks,
            'certificates' => $certificates,
            'latestCertificate' => $latestCertificate,
            'completedCoursesCount' => $completedCoursesCount,
            'documents' => $documents,
        ]);
    }
}
