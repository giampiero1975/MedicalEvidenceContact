<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'legal_name',
        'company_type',
        'vat_number',
        'tax_code',
        'description',
        'website',
        'email',
        'phone',
        'pec',
        'logo_path',
        'location',
        'employee_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointsOfContact(): HasMany
    {
        return $this->hasMany(BusinessPointOfContact::class);
    }

    public function primaryPointOfContact(): HasOne
    {
        return $this->hasOne(BusinessPointOfContact::class)->oldestOfMany();
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    /**
     * @param  array{first_name:string,last_name:string,email:string,phone?:string|null}  $data
     */
    public function addPointOfContact(array $data): BusinessPointOfContact
    {
        return $this->pointsOfContact()->create($data);
    }
}
