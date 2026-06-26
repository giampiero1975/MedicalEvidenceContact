<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $this->validateJobPostingFilters($request);

        $jobPostings = JobPosting::query()
            ->with([
                'businessProfile',
                'applications' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->when($user->role === 'professional', fn ($query) => $query->visibleToProfessionals())
            ->when($user->role === 'business', fn ($query) => $query->where('user_id', $user->id))
            ->when($filters['keyword'] ?? null, function ($query, string $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where('title', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('required_skills', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['location'] ?? null, fn ($query, string $location) => $query->where('workplace_address', 'like', "%{$location}%"))
            ->when($filters['contract_type'] ?? null, fn ($query, string $contractType) => $query->where('contract_type', $contractType))
            ->when($filters['company_category'] ?? null, function ($query, string $companyCategory) {
                $query->whereHas('businessProfile', fn ($profile) => $profile->where('company_type', 'like', "%{$companyCategory}%"));
            })
            ->when($filters['professional_category'] ?? null, function ($query, string $professionalCategory) {
                $query->where(function ($query) use ($professionalCategory) {
                    $query
                        ->where('title', 'like', "%{$professionalCategory}%")
                        ->orWhere('required_skills', 'like', "%{$professionalCategory}%");
                });
            })
            ->when($filters['salary_min'] ?? null, function ($query, string $salaryMin) {
                $query->where(function ($query) use ($salaryMin) {
                    $query
                        ->whereNull('salary_max')
                        ->orWhere('salary_max', '>=', $salaryMin);
                });
            })
            ->when($filters['salary_max'] ?? null, function ($query, string $salaryMax) {
                $query->where(function ($query) use ($salaryMax) {
                    $query
                        ->whereNull('salary_min')
                        ->orWhere('salary_min', '<=', $salaryMax);
                });
            })
            ->when($filters['published_from'] ?? null, fn ($query, string $publishedFrom) => $query->whereDate('created_at', '>=', $publishedFrom))
            ->when($filters['published_to'] ?? null, fn ($query, string $publishedTo) => $query->whereDate('created_at', '<=', $publishedTo))
            ->when(
                $user->role === 'business' && ($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $acceptedJobApplications = $user->role === 'professional'
            ? $user->jobApplications()
                ->with('jobPosting')
                ->latest()
                ->get()
            : collect();

        return view('job-postings.index', [
            'jobPostings' => $jobPostings,
            'acceptedJobApplications' => $acceptedJobApplications,
            'filters' => $filters,
            'contractTypes' => $this->contractTypes(),
            'role' => $user->role,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->role === 'business', 403);

        return view('job-postings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);

        $data = $this->validateJobPosting($request);

        $businessProfile = $request->user()->businessProfile;

        JobPosting::create([
            ...$data,
            'user_id' => $request->user()->id,
            'business_profile_id' => $businessProfile?->id,
            'status' => 'active',
        ]);

        return redirect()
            ->route('job-postings.index')
            ->with('status', 'Annuncio pubblicato.');
    }

    public function show(Request $request, JobPosting $jobPosting): View
    {
        $user = $request->user();

        abort_unless(
            ($user->role === 'business' && $jobPosting->user_id === $user->id)
            || ($user->role === 'professional' && $jobPosting->status === 'active' && $jobPosting->expires_at->toDateString() >= now()->toDateString()),
            403
        );

        $jobPosting->load([
            'applications' => fn ($query) => $query->where('user_id', $user->id),
        ]);

        return view('job-postings.show', [
            'jobPosting' => $jobPosting,
            'role' => $user->role,
        ]);
    }

    public function edit(Request $request, JobPosting $jobPosting): View
    {
        $this->authorizeBusinessOwner($request, $jobPosting);

        return view('job-postings.edit', [
            'jobPosting' => $jobPosting,
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeBusinessOwner($request, $jobPosting);

        $jobPosting->update([
            ...$this->validateJobPosting($request),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()
            ->route('job-postings.show', $jobPosting)
            ->with('status', 'Annuncio aggiornato.');
    }

    public function destroy(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeBusinessOwner($request, $jobPosting);

        $jobPosting->delete();

        return redirect()
            ->route('job-postings.index')
            ->with('status', 'Annuncio eliminato.');
    }

    public function applications(Request $request, JobPosting $jobPosting): View
    {
        abort_unless($request->user()->role === 'business', 403);
        abort_unless($jobPosting->user_id === $request->user()->id, 403);

        $jobPosting->load([
            'applications' => fn ($query) => $query
                ->with(['professional:id,name,first_name,last_name,role,residence', 'professional.professionalProfileItems' => fn ($items) => $items->latest()])
                ->latest(),
        ]);

        return view('job-postings.applications', [
            'jobPosting' => $jobPosting,
            'applications' => $jobPosting->applications,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJobPosting(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'positions' => ['required', 'integer', 'min:1', 'max:1000'],
            'workplace_address' => ['required', 'string', 'max:255'],
            'required_skills' => ['nullable', 'string', 'max:3000'],
            'contract_type' => ['required', 'string', 'max:120'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'expires_at' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['sometimes', 'in:active,expired'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJobPostingFilters(Request $request): array
    {
        return $request->validate([
            'keyword' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'contract_type' => ['nullable', 'string', 'max:120'],
            'company_category' => ['nullable', 'string', 'max:120'],
            'professional_category' => ['nullable', 'string', 'max:120'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'published_from' => ['nullable', 'date'],
            'published_to' => ['nullable', 'date', 'after_or_equal:published_from'],
            'status' => ['nullable', 'in:active,expired'],
        ]);
    }

    /**
     * @return list<string>
     */
    private function contractTypes(): array
    {
        return ['Tempo indeterminato', 'Tempo determinato', 'Part-time', 'Collaborazione', 'Libero professionista', 'Somministrazione'];
    }

    private function authorizeBusinessOwner(Request $request, JobPosting $jobPosting): void
    {
        abort_unless($request->user()->role === 'business', 403);
        abort_unless($jobPosting->user_id === $request->user()->id, 403);
    }
}
