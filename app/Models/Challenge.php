<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'goal_type',
        'goal_value',
        'goal_unit',
        'start_date',
        'end_date',
        'max_participants',
        'is_active',
        'badge_id',
    ];

    protected $casts = [
        'goal_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class)->where('status', 'active');
    }

    public function isJoinedBy(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function isOngoing(): bool
    {
        $now = now()->toDateString();
        return $this->is_active && $this->start_date <= $now && $this->end_date >= $now;
    }

    public function hasEnded(): bool
    {
        return $this->end_date < now()->toDateString();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOngoing($query)
    {
        $now = now()->toDateString();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now()->toDateString());
    }
}
