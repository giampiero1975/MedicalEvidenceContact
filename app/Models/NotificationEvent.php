<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'frequency',
        'subject',
        'heading',
        'intro',
        'action_label',
        'action_url',
        'details',
        'secondary_action_label',
        'secondary_action_url',
        'occurred_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'occurred_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
