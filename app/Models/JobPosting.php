<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_profile_id',
        'external_company_name',
        'business_location_id',
        'business_department_id',
        'title',
        'description',
        'professional_category',
        'positions',
        'workplace_address',
        'workplace_city',
        'workplace_province',
        'required_skills',
        'benefits',
        'preferred_requirements',
        'work_schedule',
        'contract_type',
        'salary_min',
        'salary_max',
        'expires_at',
        'expiry_reminder_sent_at',
        'suspended_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'expiry_reminder_sent_at' => 'datetime',
            'suspended_at' => 'datetime',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
        ];
    }

    public function setDescriptionAttribute(?string $value): void
    {
        $safe = strip_tags((string) $value, '<p><br><strong><b><em><i><u><ul><ol><li>');
        $safe = preg_replace('/<(p|br|strong|b|em|i|u|ul|ol|li)\b[^>]*>/iu', '<$1>', $safe) ?? $safe;

        $this->attributes['description'] = trim($safe);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function businessLocation(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class);
    }

    public function businessDepartment(): BelongsTo
    {
        return $this->belongsTo(BusinessDepartment::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function companyName(): string
    {
        return $this->external_company_name
            ?: $this->businessProfile?->company_name
            ?: $this->owner?->businessProfile?->company_name
            ?: $this->owner?->name
            ?: 'Struttura non specificata';
    }

    public function scopeVisibleToProfessionals(Builder $query): Builder
    {
        return $query
            ->whereNull('suspended_at')
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now()->toDateString());
    }
}
