<?php

namespace App\Providers;

use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Response 매크로 등록
        Response::macro('success', function (mixed $data = null, string $message = '', int $code = 200) {
            return ApiResponse::success($data, $message, $code);
        });

        Response::macro('error', function (string $message = '오류가 발생했습니다.', mixed $errors = null, int $code = 400) {
            return ApiResponse::error($message, $errors, $code);
        });

        Response::macro('created', function (mixed $data = null, string $message = '생성되었습니다.') {
            return ApiResponse::created($data, $message);
        });

        Response::macro('noContent', function () {
            return ApiResponse::noContent();
        });

        Response::macro('validationError', function (mixed $errors, string $message = '입력값을 확인해주세요.') {
            return ApiResponse::validationError($errors, $message);
        });

        Response::macro('unauthorized', function (string $message = '인증이 필요합니다.') {
            return ApiResponse::unauthorized($message);
        });

        Response::macro('forbidden', function (string $message = '권한이 없습니다.') {
            return ApiResponse::forbidden($message);
        });

        Response::macro('notFound', function (string $message = '요청한 리소스를 찾을 수 없습니다.') {
            return ApiResponse::notFound($message);
        });
    }
}
