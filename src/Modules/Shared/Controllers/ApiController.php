<?php

namespace Modules\Shared\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Shared\Responses\ApiResponse;

abstract class ApiController extends Controller
{
    /**
     * @param array<string, mixed> $meta
     */
    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status, $meta);
    }

    protected function created(mixed $data = null, string $message = 'Created.'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    protected function noContent(): JsonResponse
    {
        return ApiResponse::noContent();
    }
}
