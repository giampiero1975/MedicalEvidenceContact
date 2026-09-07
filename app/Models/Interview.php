<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory;

    public const STATUS_PROPOSED = 'proposed';
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Legacy status created before the documented multi-slot workflow.
     * It is treated as an available proposed slot for backward compatibility.
     */
    public const STATUS_LEGACY_SCHEDULED = 'scheduled';

    protected $fillable = [
        'job_application_id',
        'business_user_id',
        'scheduled_at',
        'duration_minutes',
        'mode',
        'location',
        'notes',
        'status',
        'contact_sharing_consent',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'contact_sharing_consent' => 'boolean',
            'responded_at' => 'datetime',
        ];
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function businessUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_user_id');
    }

    public function modeLabel(): string
    {
        return match ($this->mode) {
            'video' => 'Videochiamata',
            'phone' => 'Telefonico',
            default => 'In presenza',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PROPOSED, self::STATUS_LEGACY_SCHEDULED => 'Slot proposto',
            self::STATUS_REQUESTED => 'Richiesto',
            self::STATUS_ACCEPTED => 'Accettato',
            self::STATUS_DECLINED => 'Rifiutato',
            self::STATUS_COMPLETED => 'Completato',
            self::STATUS_CANCELLED => 'Annullato',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function isAvailableProposal(): bool
    {
        return in_array($this->status, [self::STATUS_PROPOSED, self::STATUS_LEGACY_SCHEDULED], true);
    }

    public function unlocksContacts(): bool
    {
        return $this->status === self::STATUS_ACCEPTED && $this->contact_sharing_consent;
    }
}
