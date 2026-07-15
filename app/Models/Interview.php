<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'business_user_id',
        'scheduled_at',
        'duration_minutes',
        'mode',
        'location',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
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
}
