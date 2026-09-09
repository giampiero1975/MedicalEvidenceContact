<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminJobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['active', 'expired'])],
            'published_from' => ['nullable', 'date'],
            'published_to' => ['nullable', 'date', 'after_or_equal:published_from'],
            'expires_from' => ['nullable', 'date'],
            'expires_to' => ['nullable', 'date', 'after_or_equal:expires_from'],
        ]);

        $jobPostings = JobPosting::query()
            ->with(['owner', 'businessProfile'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['published_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['published_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['expires_from'] ?? null, fn ($query, string $date) => $query->whereDate('expires_at', '>=', $date))
            ->when($filters['expires_to'] ?? null, fn ($query, string $date) => $query->whereDate('expires_at', '<=', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.job-postings.index', [
            'jobPostings' => $jobPostings,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.job-postings.create', [
            'jobPosting' => new JobPosting(['positions' => 1, 'status' => 'active']),
            'businessUsers' => $this->businessUsers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validateJobPosting($request);
        $owner = User::with('businessProfile')->findOrFail($data['user_id']);

        $jobPosting = JobPosting::create([
            ...$data,
            'business_profile_id' => $owner->businessProfile?->id,
            'status' => $data['status'] ?? 'active',
        ]);

        return redirect()
            ->route('admin.job-postings.edit', $jobPosting)
            ->with('status', 'Annuncio creato.');
    }

    public function edit(Request $request, JobPosting $jobPosting): View
    {
        $this->authorizeAdmin($request);

        return view('admin.job-postings.edit', [
            'jobPosting' => $jobPosting,
            'businessUsers' => $this->businessUsers(),
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validateJobPosting($request);
        $owner = User::with('businessProfile')->findOrFail($data['user_id']);

        $jobPosting->update([
            ...$data,
            'business_profile_id' => $owner->businessProfile?->id,
        ]);

        return redirect()
            ->route('admin.job-postings.edit', $jobPosting)
            ->with('status', 'Annuncio aggiornato.');
    }

    public function destroy(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $jobPosting->delete();

        return redirect()
            ->route('admin.job-postings.index')
            ->with('status', 'Annuncio eliminato.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJobPosting(Request $request): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'business'),
            ],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'positions' => ['required', 'integer', 'min:1', 'max:1000'],
            'workplace_address' => ['required', 'string', 'max:255'],
            'required_skills' => ['nullable', 'string', 'max:3000'],
            'contract_type' => ['required', 'string', 'max:120'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'expires_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'expired'])],
        ]);
    }

    private function businessUsers()
    {
        return User::query()
            ->where('role', 'business')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
