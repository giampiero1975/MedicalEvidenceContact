<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'name',
        'type',
        'street_address',
        'city',
        'province',
        'postal_code',
        'country',
        'email',
        'phone',
        'is_primary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function formattedAddress(): string
    {
        $cityLine = collect([$this->postal_code, $this->city])
            ->filter()
            ->implode(' ');

        if ($this->province) {
            $cityLine .= ($cityLine !== '' ? ' ' : '').'('.$this->province.')';
        }

        return collect([
            $this->street_address,
            $cityLine,
            $this->country,
        ])->filter()->implode(', ');
    }
}
