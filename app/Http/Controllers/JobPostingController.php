<?php

namespace App\Http\Controllers;

use App\Mail\TransactionalActionMail;
use App\Models\BusinessDepartment;
use App\Models\BusinessLocation;
use App\Models\BusinessType;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $this->validateJobPostingFilters($request);
        $businessProfileId = $user->role === 'business' ? $user->businessContextProfile()?->id : null;

        $jobPostings = JobPosting::query()
            ->with([
                'businessProfile',
                'businessLocation',
                'businessDepartment',
                'applications' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->when($user->role === 'professional', fn ($query) => $query->visibleToProfessionals())
            ->when($user->role === 'business', function ($query) use ($user, $businessProfileId) {
                if ($businessProfileId) {
                    $query->where(function ($query) use ($user, $businessProfileId) {
                        $query
                            ->where('business_profile_id', $businessProfileId)
                            ->orWhere(function ($query) use ($user) {
                                $query->whereNull('business_profile_id')->where('user_id', $user->id);
                            });
                    });

                    return;
                }

                $query->where('user_id', $user->id);
            })
            ->when($filters['keyword'] ?? null, function ($query, string $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where('title', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('required_skills', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['location'] ?? null, fn ($query, string $location) => $query->where('workplace_address', 'like', "%{$location}%"))
            ->when(
                $user->role === 'professional' && ! empty($filters['contract_types'] ?? []),
                fn ($query) => $query->whereIn('contract_type', $filters['contract_types'])
            )
            ->when(
                $user->role === 'business' && ($filters['contract_type'] ?? null),
                fn ($query, string $contractType) => $query->where('contract_type', $contractType)
            )
            ->when(
                $user->role === 'professional' && ! empty($filters['company_categories'] ?? []),
                fn ($query) => $query->whereHas(
                    'businessProfile',
                    fn ($profile) => $profile->whereIn('company_type', $filters['company_categories'])
                )
            )
            ->when(
                $user->role === 'business' && ($filters['company_category'] ?? null),
                fn ($query, string $companyCategory) => $query->whereHas(
                    'businessProfile',
                    fn ($profile) => $profile->where('company_type', 'like', "%{$companyCategory}%")
                )
            )
            ->when($filters['professional_category'] ?? null, function ($query, string $professionalCategory) {
                $query->where(function ($query) use ($professionalCategory) {
                    $query
                        ->where('title', 'like', "%{$professionalCategory}%")
                        ->orWhere('required_skills', 'like', "%{$professionalCategory}%");
                });
            })
            ->when($filters['salary_min'] ?? null, function ($query, string $salaryMin) {
                $query->where(function ($query) use ($salaryMin) {
                    $query->whereNull('salary_max')->orWhere('salary_max', '>=', $salaryMin);
                });
            })
            ->when($filters['salary_max'] ?? null, function ($query, string $salaryMax) {
                $query->where(function ($query) use ($salaryMax) {
                    $query->whereNull('salary_min')->orWhere('salary_min', '<=', $salaryMax);
                });
            })
            ->when(
                $user->role === 'professional' && ($filters['publication_period'] ?? null) === 'week',
                fn ($query) => $query->where('created_at', '>=', now()->subWeek())
            )
            ->when(
                $user->role === 'professional' && ($filters['publication_period'] ?? null) === 'month',
                fn ($query) => $query->where('created_at', '>=', now()->subMonth())
            )
            ->when($filters['published_from'] ?? null, fn ($query, string $publishedFrom) => $query->whereDate('created_at', '>=', $publishedFrom))
            ->when($filters['published_to'] ?? null, fn ($query, string $publishedTo) => $query->whereDate('created_at', '<=', $publishedTo))
            ->when(
                $user->role === 'business' && ($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $acceptedJobApplications = $user->role === 'professional'
            ? $user->jobApplications()->with('jobPosting')->latest()->get()
            : collect();

        return view('job-postings.index', [
            'jobPostings' => $jobPostings,
            'acceptedJobApplications' => $acceptedJobApplications,
            'filters' => $filters,
            'contractTypes' => $this->contractTypes(),
            'companyCategories' => $this->companyCategories(),
            'role' => $user->role,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->role === 'business', 403);

        return view('job-postings.create', [
            'businessLocations' => $this->availableLocations($request),
            'businessDepartments' => $this->availableDepartments($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);

        $businessProfile = $request->user()->businessContextProfile();
        $data = $this->validatedJobPostingData($request, $businessProfile?->id);

        $jobPosting = JobPosting::create([
            ...$data,
            'user_id' => $request->user()->id,
            'business_profile_id' => $businessProfile?->id,
            'status' => 'active',
        ]);

        if ($request->user()->email) {
            Mail::to($request->user()->email)->send(new TransactionalActionMail(
                mailSubject: 'Annuncio pubblicato: '.$jobPosting->title,
                heading: 'Annuncio pubblicato',
                intro: 'Il tuo annuncio è stato pubblicato correttamente ed è ora disponibile ai professionisti.',
                actionLabel: 'Apri annuncio',
                actionUrl: route('job-postings.show', $jobPosting),
                details: [
                    'Annuncio: '.$jobPosting->title,
                    'Scadenza: '.$jobPosting->expires_at->format('d/m/Y'),
                ],
            ));
        }

        return redirect()->route('job-postings.index')->with('status', 'Annuncio pubblicato.');
    }

    public function show(Request $request, JobPosting $jobPosting): View
    {
        $user = $request->user();

        abort_unless(
            ($user->role === 'business' && $this->businessCanAccessJobPosting($user, $jobPosting))
            || ($user->role === 'professional' && $jobPosting->status === 'active' && $jobPosting->expires_at->toDateString() >= now()->toDateString()),
            403
        );

        $jobPosting->load([
            'businessLocation',
            'businessDepartment',
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
            'businessLocations' => $this->availableLocations($request),
            'businessDepartments' => $this->availableDepartments($request),
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeBusinessOwner($request, $jobPosting);

        $jobPosting->update([
            ...$this->validatedJobPostingData($request, $jobPosting->business_profile_id),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('job-postings.show', $jobPosting)->with('status', 'Annuncio aggiornato.');
    }

    public function destroy(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeBusinessOwner($request, $jobPosting);

        $terminalStatuses = [
            JobApplication::STATUS_REJECTED,
            JobApplication::STATUS_WITHDRAWN,
            JobApplication::STATUS_HIRED,
        ];

        if ($jobPosting->applications()->whereNotIn('status', $terminalStatuses)->exists()) {
            return redirect()
                ->route('job-postings.show', $jobPosting)
                ->with('warning', 'Non puoi eliminare un annuncio con candidature attive. Puoi chiuderlo impostando lo stato su Scaduto.');
        }

        $jobPosting->delete();

        return redirect()->route('job-postings.index')->with('status', 'Annuncio eliminato.');
    }

    public function applications(Request $request, JobPosting $jobPosting): View
    {
        abort_unless($request->user()->role === 'business', 403);
        abort_unless($this->businessCanAccessJobPosting($request->user(), $jobPosting), 403);

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

    private function validatedJobPostingData(Request $request, ?int $businessProfileId): array
    {
        $request->merge([
            'salary_min' => $this->normalizeMoney($request->input('salary_min')),
            'salary_max' => $this->normalizeMoney($request->input('salary_max')),
        ]);

        $minimumExpiryDate = today()->addDays(7)->toDateString();

        $data = $request->validate([
            'business_location_id' => [
                'nullable',
                'integer',
                Rule::exists('business_locations', 'id')->where(
                    fn ($query) => $query
                        ->where('business_profile_id', $businessProfileId)
                        ->where('is_active', true)
                ),
            ],
            'business_department_id' => [
                'nullable',
                'integer',
                Rule::exists('business_departments', 'id')->where(
                    fn ($query) => $query
                        ->where('business_profile_id', $businessProfileId)
                        ->where('is_active', true)
                ),
            ],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'positions' => ['required', 'integer', 'min:1', 'max:1000'],
            'workplace_address' => ['required_without:business_location_id', 'nullable', 'string', 'max:255'],
            'required_skills' => ['nullable', 'string', 'max:3000'],
            'contract_type' => ['required', 'string', 'max:120'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'expires_at' => ['required', 'date', 'after_or_equal:'.$minimumExpiryDate],
            'status' => ['sometimes', 'in:active,expired'],
        ], [
            'business_location_id.exists' => 'La sede selezionata non è disponibile per questa struttura.',
            'business_department_id.exists' => 'Il reparto selezionato non è disponibile per questa struttura.',
            'workplace_address.required_without' => 'Seleziona una sede oppure inserisci un indirizzo di lavoro.',
            'salary_min.numeric' => 'La retribuzione minima deve essere un importo valido.',
            'salary_max.numeric' => 'La retribuzione massima deve essere un importo valido.',
            'salary_max.gte' => 'La retribuzione massima deve essere uguale o superiore alla retribuzione minima.',
            'expires_at.after_or_equal' => 'La data di scadenza deve essere almeno 7 giorni da oggi.',
        ]);

        if (! empty($data['business_location_id'])) {
            $location = BusinessLocation::query()
                ->whereKey($data['business_location_id'])
                ->where('business_profile_id', $businessProfileId)
                ->where('is_active', true)
                ->firstOrFail();

            $data['workplace_address'] = $location->formattedAddress();
        }

        if (! empty($data['business_department_id'])) {
            $department = BusinessDepartment::query()
                ->whereKey($data['business_department_id'])
                ->where('business_profile_id', $businessProfileId)
                ->where('is_active', true)
                ->firstOrFail();

            if (empty($data['business_location_id']) || $department->business_location_id !== (int) $data['business_location_id']) {
                validator([], [
                    'business_department_id' => fn () => false,
                ], [
                    'business_department_id' => 'Il reparto selezionato non appartiene alla sede indicata.',
                ])->validate();
            }
        }

        return $data;
    }

    private function validateJobPostingFilters(Request $request): array
    {
        return $request->validate([
            'keyword' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'contract_type' => ['nullable', 'string', 'max:120'],
            'contract_types' => ['nullable', 'array'],
            'contract_types.*' => ['string', 'max:120', Rule::in($this->contractTypes())],
            'company_category' => ['nullable', 'string', 'max:120'],
            'company_categories' => ['nullable', 'array'],
            'company_categories.*' => ['string', 'max:120', Rule::in($this->companyCategories())],
            'professional_category' => ['nullable', 'string', 'max:120'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'publication_period' => ['nullable', 'in:recent,week,month'],
            'published_from' => ['nullable', 'date'],
            'published_to' => ['nullable', 'date', 'after_or_equal:published_from'],
            'status' => ['nullable', 'in:active,expired'],
        ]);
    }

    private function availableLocations(Request $request)
    {
        return $request->user()->businessContextProfile()?->locations()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get() ?? collect();
    }

    private function availableDepartments(Request $request)
    {
        return $request->user()->businessContextProfile()?->departments()
            ->with('location')
            ->where('is_active', true)
            ->orderBy('name')
            ->get() ?? collect();
    }

    private function normalizeMoney(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.-]/u', '', trim((string) $value));

        if ($normalized === null || $normalized === '') {
            return (string) $value;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                return str_replace(',', '.', str_replace('.', '', $normalized));
            }

            return str_replace(',', '', $normalized);
        }

        if (str_contains($normalized, ',')) {
            return str_replace('.', '', str_replace(',', '.', $normalized));
        }

        if (substr_count($normalized, '.') === 1 && preg_match('/\.\d{3}$/', $normalized) === 1) {
            return str_replace('.', '', $normalized);
        }

        if (substr_count($normalized, '.') > 1) {
            return str_replace('.', '', $normalized);
        }

        return $normalized;
    }

    private function contractTypes(): array
    {
        return ['Tempo indeterminato', 'Tempo determinato', 'Part-time', 'Collaborazione', 'Libero professionista', 'Somministrazione'];
    }

    private function companyCategories(): array
    {
        return BusinessType::query()
            ->active()
            ->ordered()
            ->pluck('name')
            ->all();
    }

    private function businessCanAccessJobPosting($user, JobPosting $jobPosting): bool
    {
        if ($jobPosting->user_id === $user->id) {
            return true;
        }

        $businessProfileId = $user->businessContextProfile()?->id;

        return $businessProfileId !== null
            && $jobPosting->business_profile_id !== null
            && (int) $jobPosting->business_profile_id === (int) $businessProfileId;
    }

    private function authorizeBusinessOwner(Request $request, JobPosting $jobPosting): void
    {
        abort_unless($request->user()->role === 'business', 403);
        abort_unless($this->businessCanAccessJobPosting($request->user(), $jobPosting), 403);
    }
}
