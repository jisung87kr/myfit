<?php

namespace App\Enums;

enum QuestionType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case SELECT = 'select';
    case MULTI_SELECT = 'multi_select';

    /**
     * 질문 유형의 한글 표시명
     */
    public function displayName(): string
    {
        return match ($this) {
            self::TEXT => '텍스트 입력',
            self::NUMBER => '숫자 입력',
            self::SELECT => '단일 선택',
            self::MULTI_SELECT => '다중 선택',
        };
    }

    /**
     * 선택형 질문인지 확인
     */
    public function isSelectable(): bool
    {
        return in_array($this, [self::SELECT, self::MULTI_SELECT]);
    }

    /**
     * options 필드가 필요한 타입인지 확인
     */
    public function requiresOptions(): bool
    {
        return $this->isSelectable();
    }

    /**
     * 답변 검증 규칙 반환
     */
    public function validationRule(): string
    {
        return match ($this) {
            self::TEXT => 'string|max:1000',
            self::NUMBER => 'numeric',
            self::SELECT => 'string',
            self::MULTI_SELECT => 'array',
        };
    }

    /**
     * 모든 사용 가능한 질문 유형 반환
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
