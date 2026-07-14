<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    public const STATUS_RECEIVED = 'inviata';
    public const STATUS_REVIEW = 'in_valutazione';
    public const STATUS_INTERVIEW_SCHEDULED = 'colloquio_programmato';
    public const STATUS_INTERVIEW_COMPLETED = 'colloquio_effettuato';
    public const STATUS_SUITABLE = 'idoneo';
    public const STATUS_HIRED = 'assunto';
    public const STATUS_REJECTED = 'non_idoneo';
    public const STATUS_WITHDRAWN = 'ritirata';

    protected $fillable = [
        'job_posting_id',
        'user_id',
        'status',
    ];

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_RECEIVED => 'Ricevuta',
            self::STATUS_REVIEW => 'In valutazione',
            self::STATUS_INTERVIEW_SCHEDULED => 'Colloquio programmato',
            self::STATUS_INTERVIEW_COMPLETED => 'Colloquio effettuato',
            self::STATUS_SUITABLE => 'Idoneo',
            self::STATUS_HIRED => 'Assunto',
            self::STATUS_REJECTED => 'Non idoneo',
            self::STATUS_WITHDRAWN => 'Ritirata',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
