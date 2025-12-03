<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 설문에 속한 모든 질문
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    /**
     * 설문에 대한 모든 사용자 응답
     */
    public function responses(): HasMany
    {
        return $this->hasMany(UserSurveyResponse::class);
    }

    /**
     * 특정 단계의 질문들
     */
    public function questionsByStep(int $step): HasMany
    {
        return $this->questions()->where('step', $step);
    }

    /**
     * 활성화된 설문만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 설문 총 질문 수
     */
    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }

    /**
     * 특정 사용자가 설문을 완료했는지 확인
     */
    public function isCompletedBy(User $user): bool
    {
        $totalQuestions = $this->total_questions;
        $answeredQuestions = $this->responses()
            ->where('user_id', $user->id)
            ->distinct('survey_question_id')
            ->count();

        return $totalQuestions > 0 && $answeredQuestions === $totalQuestions;
    }

    /**
     * 특정 사용자의 응답 진행률 (0-100)
     */
    public function getProgressByUser(User $user): int
    {
        $totalQuestions = $this->total_questions;
        if ($totalQuestions === 0) {
            return 0;
        }

        $answeredQuestions = $this->responses()
            ->where('user_id', $user->id)
            ->distinct('survey_question_id')
            ->count();

        return (int) (($answeredQuestions / $totalQuestions) * 100);
    }
}
