<?php

namespace App\Http\Controllers;

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

        $moodleSites = MoodleSite::query()
            ->where('enabled', true)
            ->orderBy('name')
            ->get();

        $moodleUserLinks = $user
            ->moodleUserLinks()
            ->with('moodleSite')
            ->latest()
            ->get();

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

        $completedFields = collect($profileFields)
            ->filter(fn ($value) => filled($value))
            ->count();

        $profileCompletion = (int) round(($completedFields / count($profileFields)) * 100);

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

        return view('professionals.dashboard-overview', [
            'jobApplications' => $jobApplications,
            'activeApplicationsCount' => $jobApplications->whereNotIn('status', ['rifiutata', 'ritirata'])->count(),
            'acceptedApplicationsCount' => $jobApplications->where('status', 'accettata')->count(),
            'availableJobsCount' => JobPosting::query()->visibleToProfessionals()->count(),
            'profileCompletion' => $profileCompletion,
            'profileItems' => $profileItems,
            'moodleSites' => $moodleSites,
            'moodleUserLinks' => $moodleUserLinks,
            'documents' => $documents,
        ]);
    }
}
