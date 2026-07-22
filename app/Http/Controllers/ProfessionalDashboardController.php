<?php

namespace App\Http\Controllers;

use App\Models\MoodleSite;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProfessionalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->role === 'professional', 403);

        $applications = $user->jobApplications()
            ->with('jobPosting.owner.businessProfile')
            ->latest()
            ->get();

        $profileItems = $user->professionalProfileItems()
            ->latest()
            ->get();

        $moodleSites = MoodleSite::query()
            ->where('enabled', true)
            ->orderBy('name')
            ->get();

        $moodleUserLinks = $user->moodleUserLinks()
            ->with('moodleSite')
            ->latest()
            ->get();

        $certificateCount = $user->certificates()->count();
        $nationality = strtolower(trim((string) $user->nationality));
        $isItalian = in_array($nationality, ['italiana', 'italiano', 'italia', 'italian'], true);

        $profileChecks = collect([
            [
                'label' => 'Dati personali',
                'complete' => filled($user->first_name) && filled($user->last_name) && filled($user->phone),
            ],
            [
                'label' => 'Residenza e nazionalita',
                'complete' => filled($user->residence) && filled($user->nationality),
            ],
            [
                'label' => 'Professione',
                'complete' => $user->professionalProfession()->exists(),
            ],
            [
                'label' => 'Esperienze o studi',
                'complete' => $profileItems->isNotEmpty(),
            ],
            [
                'label' => 'Attestato ATA',
                'complete' => filled($user->ata_certificate_path),
            ],
            [
                'label' => 'Permesso di soggiorno',
                'complete' => $isItalian || filled($user->residence_permit_path),
            ],
            [
                'label' => 'Collegamento Moodle',
                'complete' => $moodleUserLinks->isNotEmpty(),
            ],
        ]);

        $completedChecks = $profileChecks->where('complete', true)->count();
        $profileCompletion = (int) round(($completedChecks / max($profileChecks->count(), 1)) * 100);

        $interviewStatuses = ['interview', 'interview_scheduled', 'colloquio', 'colloquio_fissato'];
        $interviewApplications = $applications
            ->filter(fn ($application) => in_array(strtolower((string) $application->status), $interviewStatuses, true));

        $recentActivity = $this->buildRecentActivity(
            $applications,
            $profileItems,
            $moodleUserLinks,
            $certificateCount,
        );

        return view('professionals.dashboard', [
            'acceptedJobApplications' => $applications,
            'applications' => $applications,
            'certificateCount' => $certificateCount,
            'interviewApplications' => $interviewApplications,
            'isItalian' => $isItalian,
            'moodleSites' => $moodleSites,
            'moodleUserLinks' => $moodleUserLinks,
            'profileChecks' => $profileChecks,
            'profileCompletion' => $profileCompletion,
            'profileItems' => $profileItems,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function buildRecentActivity(
        Collection $applications,
        Collection $profileItems,
        Collection $moodleUserLinks,
        int $certificateCount,
    ): Collection {
        $activity = collect();

        foreach ($applications->take(3) as $application) {
            $activity->push([
                'date' => $application->created_at,
                'title' => 'Candidatura inviata',
                'description' => $application->jobPosting?->title ?? 'Opportunita professionale',
            ]);
        }

        foreach ($profileItems->take(2) as $item) {
            $activity->push([
                'date' => $item->created_at,
                'title' => $item->type === 'education' ? 'Percorso di studio aggiunto' : 'Esperienza aggiunta',
                'description' => $item->title,
            ]);
        }

        if ($moodleUserLinks->isNotEmpty()) {
            $activity->push([
                'date' => $moodleUserLinks->first()->updated_at,
                'title' => 'Account Moodle collegato',
                'description' => $certificateCount > 0
                    ? $certificateCount.' attestati disponibili'
                    : 'Collegamento pronto per la sincronizzazione',
            ]);
        }

        return $activity
            ->sortByDesc('date')
            ->take(5)
            ->values();
    }
}
