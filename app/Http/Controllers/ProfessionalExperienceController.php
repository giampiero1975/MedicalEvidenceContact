<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalExperienceController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->role === 'professional', 403);

        return view('professionals.experiences.index', [
            'profileItems' => $request->user()
                ->professionalProfileItems()
                ->latest()
                ->get(),
        ]);
    }
}
