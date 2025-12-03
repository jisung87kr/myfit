<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveySubmission extends Model
{
    protected $fillable = [
        'user_id',
        'survey_id',
        'completion_data',
        'submitted_at',
    ];

    protected $casts = [
        'completion_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * 제출한 사용자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 제출한 설문
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
