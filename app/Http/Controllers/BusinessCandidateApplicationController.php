<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
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

        return view('business.applications.show', [
            'application' => $jobApplication,
            'professional' => $jobApplication->professional,
            'statusOptions' => JobApplication::statusOptions(),
        ]);
    }
}
