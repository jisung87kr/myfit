<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'survey_id',
        'survey_question_id',
        'answer',
        'answered_at',
    ];

    protected $casts = [
        'answer' => 'array',
        'answered_at' => 'datetime',
    ];

    /**
     * 응답한 사용자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 응답한 설문
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * 응답한 질문
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    /**
     * 특정 사용자의 응답만 조회
     */
    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * 특정 설문의 응답만 조회
     */
    public function scopeBySurvey($query, Survey $survey)
    {
        return $query->where('survey_id', $survey->id);
    }

    /**
     * 특정 단계의 응답만 조회
     */
    public function scopeByStep($query, int $step)
    {
        return $query->whereHas('question', function ($q) use ($step) {
            $q->where('step', $step);
        });
    }

    /**
     * 최근 응답 순으로 조회
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('answered_at', 'desc');
    }

    /**
     * 답변 값 반환 (JSON 디코딩 처리)
     */
    public function getAnswerValue()
    {
        $answer = $this->answer;

        // 배열인 경우 그대로 반환
        if (is_array($answer)) {
            return $answer;
        }

        // 단일 값인 경우 적절히 변환
        if (isset($answer['value'])) {
            return $answer['value'];
        }

        return $answer;
    }

    /**
     * 답변을 사람이 읽을 수 있는 형태로 변환
     */
    public function getReadableAnswer(): string
    {
        $answer = $this->getAnswerValue();

        if (is_array($answer)) {
            return implode(', ', $answer);
        }

        return (string) $answer;
    }
}
