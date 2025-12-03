<?php

namespace App\Enums;

enum SurveyStep: int
{
    case BASIC_INFO = 1;
    case GOAL_SETTING = 2;
    case LIFESTYLE = 3;
    case HEALTH_PREFERENCE = 4;
    case ADDITIONAL = 5;

    /**
     * 단계별 한글 표시명
     */
    public function displayName(): string
    {
        return match ($this) {
            self::BASIC_INFO => '기본 정보',
            self::GOAL_SETTING => '목표 설정',
            self::LIFESTYLE => '생활 패턴',
            self::HEALTH_PREFERENCE => '건강 & 선호도',
            self::ADDITIONAL => '추가 정보',
        };
    }

    /**
     * 단계별 설명
     */
    public function description(): string
    {
        return match ($this) {
            self::BASIC_INFO => '신체 정보와 기본 프로필을 입력합니다',
            self::GOAL_SETTING => '다이어트 목표와 기간을 설정합니다',
            self::LIFESTYLE => '일상 생활 패턴과 활동량을 파악합니다',
            self::HEALTH_PREFERENCE => '건강 상태와 음식 선호도를 확인합니다',
            self::ADDITIONAL => '더 정확한 플랜을 위한 추가 정보를 입력합니다',
        };
    }

    /**
     * 다음 단계 반환
     */
    public function next(): ?self
    {
        return self::tryFrom($this->value + 1);
    }

    /**
     * 이전 단계 반환
     */
    public function previous(): ?self
    {
        return self::tryFrom($this->value - 1);
    }

    /**
     * 첫 번째 단계인지 확인
     */
    public function isFirst(): bool
    {
        return $this === self::BASIC_INFO;
    }

    /**
     * 마지막 단계인지 확인
     */
    public function isLast(): bool
    {
        return $this === self::ADDITIONAL;
    }

    /**
     * 진행률 퍼센트 계산 (1-100)
     */
    public function progressPercent(): int
    {
        return (int) (($this->value / 5) * 100);
    }

    /**
     * 모든 단계 반환
     */
    public static function all(): array
    {
        return self::cases();
    }
}
