<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'role',
        'phone',
        'residence',
        'nationality',
        'address_city',
        'address_country',
        'address_province',
        'postal_code',
        'street_address',
        'residence_permit_path',
        'ata_certificate_path',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function professionalProfile(): HasOne
    {
        return $this->hasOne(ProfessionalProfile::class);
    }

    public function professionalProfessions(): HasMany
    {
        return $this->hasMany(ProfessionalProfession::class);
    }

    public function professionalProfileItems(): HasMany
    {
        return $this->hasMany(ProfessionalProfileItem::class);
    }

    public function professionalDocument(): HasOne
    {
        return $this->hasOne(ProfessionalDocument::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(ProfessionalExperience::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(ProfessionalCertificate::class);
    }

    public function moodleLinks(): HasMany
    {
        return $this->hasMany(MoodleUserLink::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function favoriteJobPostings(): BelongsToMany
    {
        return $this->belongsToMany(JobPosting::class, 'job_posting_favorites')->withTimestamps();
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function businessProfile(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function businessPointsOfContact(): HasMany
    {
        return $this->hasMany(BusinessPointOfContact::class);
    }

    public function businessLocations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class);
    }

    public function businessDepartments(): HasMany
    {
        return $this->hasMany(BusinessDepartment::class);
    }
}
