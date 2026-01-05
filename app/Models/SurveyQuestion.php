<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\SurveyStep;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'step',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'order',
    ];

    protected $casts = [
        'step' => SurveyStep::class,
        'question_type' => QuestionType::class,
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * 질문이 속한 설문
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * 특정 단계의 질문만 조회
     */
    public function scopeByStep($query, SurveyStep|int $step)
    {
        $stepValue = $step instanceof SurveyStep ? $step->value : $step;
        return $query->where('step', $stepValue);
    }

    /**
     * 필수 질문만 조회
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * 선택형 질문인지 확인
     */
    public function isSelectable(): bool
    {
        return $this->question_type->isSelectable();
    }

    /**
     * 답변 검증
     */
    public function validateAnswer($answer): bool
    {
        // 필수 질문인데 답변이 없으면 false
        if ($this->is_required && empty($answer)) {
            return false;
        }

        // 선택형 질문의 경우 options 내 값인지 확인
        if ($this->isSelectable() && !empty($answer)) {
            $validOptions = $this->options ?? [];

            if ($this->question_type === QuestionType::MULTI_SELECT) {
                // 다중 선택: 모든 답변이 옵션에 포함되어야 함
                if (!is_array($answer)) {
                    return false;
                }
                foreach ($answer as $value) {
                    if (!in_array($value, $validOptions)) {
                        return false;
                    }
                }
            } else {
                // 단일 선택: 답변이 옵션에 포함되어야 함
                if (!in_array($answer, $validOptions)) {
                    return false;
                }
            }
        }

        return true;
    }
}
