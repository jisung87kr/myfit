<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'status',
        'progress',
        'current_value',
        'joined_at',
        'completed_at',
    ];

    protected $casts = [
        'progress' => 'decimal:2',
        'current_value' => 'decimal:2',
        'joined_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updateProgress(float $currentValue): void
    {
        $this->current_value = $currentValue;
        $this->progress = min(100, ($currentValue / $this->challenge->goal_value) * 100);

        if ($this->progress >= 100 && $this->status === 'active') {
            $this->status = 'completed';
            $this->completed_at = now();
        }

        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    protected static function booted(): void
    {
        static::created(function (ChallengeParticipant $participant) {
            $participant->challenge->increment('participants_count');
        });

        static::deleted(function (ChallengeParticipant $participant) {
            $participant->challenge->decrement('participants_count');
        });
    }
}
