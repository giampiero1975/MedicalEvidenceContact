<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->role === 'professional', 403);

        $acceptedJobApplications = $request->user()
            ->jobApplications()
            ->with('jobPosting')
            ->latest()
            ->get();

        return view('professionals.dashboard', [
            'acceptedJobApplications' => $acceptedJobApplications,
        ]);
    }
}