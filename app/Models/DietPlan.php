<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'survey_submission_id',
        'status',
        'start_date',
        'end_date',
        'duration_days',
        'target_calories_per_day',
        'ai_summary',
        'generation_prompt',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_calories_per_day' => 'decimal:2',
    ];

    /**
     * Get the user that owns the diet plan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the survey submission for this plan
     */
    public function surveySubmission(): BelongsTo
    {
        return $this->belongsTo(SurveySubmission::class);
    }

    /**
     * Get all daily meal plans
     */
    public function dailyMealPlans(): HasMany
    {
        return $this->hasMany(DailyMealPlan::class);
    }

    /**
     * Get all daily exercise plans
     */
    public function dailyExercisePlans(): HasMany
    {
        return $this->hasMany(DailyExercisePlan::class);
    }

    /**
     * Scope to get active plans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get plans by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if plan is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if plan is generating
     */
    public function isGenerating(): bool
    {
        return $this->status === 'generating';
    }

    /**
     * Mark plan as active
     */
    public function markAsActive(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Mark plan as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Archive the plan
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Mark plan as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Check if plan generation failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
