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
            ->with(['owner.businessProfile', 'businessProfile'])
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
        $registeredCompany = $data['company_source'] === 'registered';
        $owner = $registeredCompany
            ? User::with('businessProfile')->findOrFail($data['user_id'])
            : $request->user();

        unset($data['company_source']);

        $jobPosting = JobPosting::create([
            ...$data,
            'user_id' => $owner->id,
            'business_profile_id' => $registeredCompany ? $owner->businessProfile?->id : null,
            'external_company_name' => $registeredCompany ? null : $data['external_company_name'],
            'status' => $data['status'] ?? 'active',
        ]);

        return redirect()->route('admin.job-postings.edit', $jobPosting)->with('status', 'Annuncio creato.');
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
        $registeredCompany = $data['company_source'] === 'registered';
        $owner = $registeredCompany
            ? User::with('businessProfile')->findOrFail($data['user_id'])
            : $request->user();

        unset($data['company_source']);

        $jobPosting->update([
            ...$data,
            'user_id' => $owner->id,
            'business_profile_id' => $registeredCompany ? $owner->businessProfile?->id : null,
            'external_company_name' => $registeredCompany ? null : $data['external_company_name'],
        ]);

        return redirect()->route('admin.job-postings.edit', $jobPosting)->with('status', 'Annuncio aggiornato.');
    }

    public function toggleSuspension(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $jobPosting->update(['suspended_at' => $jobPosting->suspended_at ? null : now()]);

        return redirect()->route('admin.job-postings.index')
            ->with('status', $jobPosting->suspended_at ? 'Pubblicazione sospesa.' : 'Pubblicazione riattivata.');
    }

    public function destroy(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $jobPosting->delete();

        return redirect()->route('admin.job-postings.index')->with('status', 'Annuncio eliminato.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }

    /** @return array<string, mixed> */
    private function validateJobPosting(Request $request): array
    {
        if (! $request->filled('company_source')) {
            $request->merge([
                'company_source' => $request->filled('external_company_name') ? 'external' : 'registered',
            ]);
        }

        $request->merge([
            'salary_min' => $this->normalizeMoney($request->input('salary_min')),
            'salary_max' => $this->normalizeMoney($request->input('salary_max')),
        ]);

        return $request->validate([
            'company_source' => ['required', Rule::in(['registered', 'external'])],
            'user_id' => ['nullable', 'required_if:company_source,registered', Rule::exists('users', 'id')->where('role', 'business')],
            'external_company_name' => ['nullable', 'required_if:company_source,external', 'string', 'max:180'],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                $plainText = trim(html_entity_decode(strip_tags((string) $value)));
                if ($plainText === '') {
                    $fail('La descrizione è obbligatoria.');
                } elseif (mb_strlen($plainText) > 3000) {
                    $fail('La descrizione non può superare 3000 caratteri.');
                }
            }],
            'professional_category' => ['nullable', Rule::in($this->professionalCategories())],
            'positions' => ['required', 'integer', 'min:1', 'max:1000'],
            'workplace_address' => ['required', 'string', 'max:255'],
            'workplace_city' => ['nullable', 'string', 'max:150'],
            'workplace_province' => ['nullable', 'string', 'max:100'],
            'required_skills' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (count($this->skillTags((string) $value)) > 10) {
                    $fail('Puoi indicare al massimo 10 abilità richieste.');
                }
            }],
            'benefits' => ['nullable', 'string'],
            'preferred_requirements' => ['nullable', 'string'],
            'work_schedule' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['required', Rule::in($this->contractTypes())],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:salary_min'],
            'expires_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'expired'])],
        ]);
    }

    private function businessUsers()
    {
        return User::query()->with('businessProfile')->where('role', 'business')->orderBy('name')->get(['id', 'name', 'email']);
    }

    private function contractTypes(): array
    {
        return ['Tempo determinato', 'Tempo indeterminato', 'A chiamata', 'Stage', 'Altro'];
    }

    private function professionalCategories(): array
    {
        return ['OSS', 'Infermiere', 'Anestesista', 'Fisioterapista', 'Altra'];
    }

    /** @return array<int, string> */
    private function skillTags(string $value): array
    {
        return collect(preg_split('/[\r\n,]+/u', $value) ?: [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->values()
            ->all();
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
            return strrpos($normalized, ',') > strrpos($normalized, '.')
                ? str_replace(',', '.', str_replace('.', '', $normalized))
                : str_replace(',', '', $normalized);
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
}
