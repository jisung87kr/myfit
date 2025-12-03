<?php

namespace App\Http\Responses;

use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response
     *
     * @param mixed $data
     * @param string $message
     * @param HttpStatus|int $status
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = '',
        HttpStatus|int $status = HttpStatus::OK
    ): JsonResponse {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        $statusCode = $status instanceof HttpStatus ? $status->value : $status;

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param mixed $errors
     * @param HttpStatus|int $status
     * @return JsonResponse
     */
    public static function error(
        string $message = '오류가 발생했습니다.',
        mixed $errors = null,
        HttpStatus|int $status = HttpStatus::BAD_REQUEST
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $statusCode = $status instanceof HttpStatus ? $status->value : $status;

        return response()->json($response, $statusCode);
    }

    /**
     * Created response (201)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public static function created(mixed $data = null, string $message = '생성되었습니다.'): JsonResponse
    {
        return self::success($data, $message, HttpStatus::CREATED);
    }

    /**
     * No content response (204)
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, HttpStatus::NO_CONTENT->value);
    }

    /**
     * Validation error response (422)
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(mixed $errors, string $message = '입력값을 확인해주세요.'): JsonResponse
    {
        return self::error($message, $errors, HttpStatus::UNPROCESSABLE_ENTITY);
    }

    /**
     * Unauthorized response (401)
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = '인증이 필요합니다.'): JsonResponse
    {
        return self::error($message, null, HttpStatus::UNAUTHORIZED);
    }

    /**
     * Forbidden response (403)
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(string $message = '권한이 없습니다.'): JsonResponse
    {
        return self::error($message, null, HttpStatus::FORBIDDEN);
    }

    /**
     * Not found response (404)
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(string $message = '요청한 리소스를 찾을 수 없습니다.'): JsonResponse
    {
        return self::error($message, null, HttpStatus::NOT_FOUND);
    }
}
