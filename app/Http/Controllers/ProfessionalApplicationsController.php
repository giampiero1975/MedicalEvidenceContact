<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfessionalApplicationsController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->role === 'professional', 403);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(JobApplication::statusOptions()))],
        ]);

        $applications = $request->user()
            ->jobApplications()
            ->with([
                'jobPosting.businessProfile',
                'jobPosting.businessLocation',
                'jobPosting.businessDepartment',
            ])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $statusCounts = $request->user()
            ->jobApplications()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count);

        return view('professionals.applications.index', [
            'applications' => $applications,
            'statusOptions' => JobApplication::statusOptions(),
            'statusCounts' => $statusCounts,
            'selectedStatus' => $filters['status'] ?? null,
        ]);
    }
}
