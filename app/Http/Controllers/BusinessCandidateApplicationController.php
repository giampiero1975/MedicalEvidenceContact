<?php

namespace App\Http\Controllers;

use App\Mail\TransactionalActionMail;
use App\Models\JobApplication;
use App\Models\JobApplicationEvent;
use App\Services\TransactionalNotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessCandidateApplicationController extends Controller
{
    public function show(Request $request, JobApplication $jobApplication): View
    {
        abort_unless($request->user()->role === 'business', 403);

        $jobApplication->loadMissing('jobPosting');

        abort_unless(
            $jobApplication->jobPosting !== null
            && (int) $jobApplication->jobPosting->user_id === (int) $request->user()->id,
            403
        );

        $jobApplication->load([
            'jobPosting.businessLocation',
            'jobPosting.businessDepartment',
            'professional.professionalProfileItems' => fn ($query) => $query->latest(),
            'professional.professionalDocument',
            'professional.certificates' => fn ($query) => $query->latest('issued_at'),
            'notes.author:id,name,first_name,last_name',
            'events.actor:id,name,first_name,last_name',
            'interviews.businessUser:id,name,first_name,last_name',
        ]);

        $profileViewAlreadyNotified = $jobApplication->events
            ->contains(fn ($event) => $event->type === 'professional_profile_viewed');

        if (! $profileViewAlreadyNotified) {
            JobApplicationEvent::create([
                'job_application_id' => $jobApplication->id,
                'actor_user_id' => $request->user()->id,
                'type' => 'professional_profile_viewed',
                'label' => 'Profilo professionista visualizzato dalla struttura',
                'metadata' => [
                    'business_user_id' => $request->user()->id,
                ],
            ]);

            if ($jobApplication->professional) {
                app(TransactionalNotificationDispatcher::class)->dispatch(
                    $jobApplication->professional,
                    'profile_views',
                    new TransactionalActionMail(
                        mailSubject: 'La struttura ha visualizzato il tuo profilo',
                        heading: 'Il tuo profilo è stato visualizzato',
                        intro: 'La struttura che ha pubblicato l’annuncio ha aperto il tuo profilo professionale associato alla candidatura.',
                        actionLabel: 'Visualizza candidature',
                        actionUrl: route('professional.applications.index'),
                        details: [
                            'Annuncio: '.$jobApplication->jobPosting->title,
                        ],
                    )
                );
            }
        }

        $canViewContacts = $jobApplication->interviews
            ->contains(fn ($interview) => $interview->unlocksContacts());

        return view('business.applications.show', [
            'application' => $jobApplication,
            'professional' => $jobApplication->professional,
            'statusOptions' => JobApplication::statusOptions(),
            'canViewContacts' => $canViewContacts,
        ]);
    }
}
