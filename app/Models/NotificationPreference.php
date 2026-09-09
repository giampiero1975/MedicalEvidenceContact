<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    public const FREQUENCY_IMMEDIATE = 'immediate';
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';

    protected $fillable = [
        'user_id',
        'category',
        'enabled',
        'frequency',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function categoriesForRole(string $role): array
    {
        $common = [
            'account' => 'Account e sicurezza',
            'interviews' => 'Colloqui',
        ];

        if ($role === 'professional') {
            return [
                ...$common,
                'profile_views' => 'Visualizzazioni profilo',
                'applications' => 'Candidature',
            ];
        }

        if ($role === 'business') {
            return [
                ...$common,
                'applications' => 'Candidature ricevute',
                'job_postings' => 'Annunci',
            ];
        }

        return $common;
    }

    public static function frequencyOptions(): array
    {
        return [
            self::FREQUENCY_IMMEDIATE => 'Immediata',
            self::FREQUENCY_DAILY => 'Digest giornaliero',
            self::FREQUENCY_WEEKLY => 'Digest settimanale',
        ];
    }
}
