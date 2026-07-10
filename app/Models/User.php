<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function businessProfile(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function professionalDocument(): HasOne
    {
        return $this->hasOne(ProfessionalDocument::class);
    }

    public function professionalProfession(): HasOne
    {
        return $this->hasOne(ProfessionalProfession::class);
    }

    public function professionalProfileItems(): HasMany
    {
        return $this->hasMany(ProfessionalProfileItem::class);
    }

    public function moodleUserLinks(): HasMany
    {
        return $this->hasMany(MoodleUserLink::class, 'laravel_user_id');
    }

    public function moodleLinkAttempts(): HasMany
    {
        return $this->hasMany(MoodleLinkAttempt::class, 'laravel_user_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(UserCertificate::class, 'laravel_user_id');
    }
}
