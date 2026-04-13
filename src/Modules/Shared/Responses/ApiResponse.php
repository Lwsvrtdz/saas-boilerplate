<?php

namespace Modules\Shared\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * @param array<string, mixed> $meta
     */
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = Response::HTTP_OK,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'meta' => (object) $meta,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Created.'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    public static function paginated(LengthAwarePaginator $paginator, mixed $data, string $message = 'OK'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_OK, [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
