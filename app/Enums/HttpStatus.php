<?php

namespace App\Enums;

enum HttpStatus: int
{
    // 2xx Success
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;

    // 4xx Client Errors
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE_ENTITY = 422;
    case TOO_MANY_REQUESTS = 429;

    // 5xx Server Errors
    case INTERNAL_SERVER_ERROR = 500;
    case SERVICE_UNAVAILABLE = 503;

    /**
     * Get description of the status code
     */
    public function description(): string
    {
        return match($this) {
            self::OK => '요청이 성공적으로 처리되었습니다.',
            self::CREATED => '리소스가 성공적으로 생성되었습니다.',
            self::ACCEPTED => '요청이 접수되었습니다.',
            self::NO_CONTENT => '요청이 성공했으나 반환할 내용이 없습니다.',
            self::BAD_REQUEST => '잘못된 요청입니다.',
            self::UNAUTHORIZED => '인증이 필요합니다.',
            self::FORBIDDEN => '접근 권한이 없습니다.',
            self::NOT_FOUND => '요청한 리소스를 찾을 수 없습니다.',
            self::METHOD_NOT_ALLOWED => '허용되지 않은 메소드입니다.',
            self::CONFLICT => '요청이 충돌했습니다.',
            self::UNPROCESSABLE_ENTITY => '입력값을 확인해주세요.',
            self::TOO_MANY_REQUESTS => '요청이 너무 많습니다.',
            self::INTERNAL_SERVER_ERROR => '서버 오류가 발생했습니다.',
            self::SERVICE_UNAVAILABLE => '서비스를 사용할 수 없습니다.',
        };
    }

    /**
     * Check if status is success (2xx)
     */
    public function isSuccess(): bool
    {
        return $this->value >= 200 && $this->value < 300;
    }

    /**
     * Check if status is client error (4xx)
     */
    public function isClientError(): bool
    {
        return $this->value >= 400 && $this->value < 500;
    }

    /**
     * Check if status is server error (5xx)
     */
    public function isServerError(): bool
    {
        return $this->value >= 500 && $this->value < 600;
    }
}
