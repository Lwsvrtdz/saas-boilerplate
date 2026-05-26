<?php

namespace Modules\Shared\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use JsonSerializable;
use Spatie\LaravelData\Contracts\TransformableData;
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
            'data' => self::normalize($data),
            'meta' => (object) $meta,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Created.'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function noContent(): HttpResponse
    {
        return response()->noContent();
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

    protected static function normalize(mixed $value): mixed
    {
        if ($value instanceof TransformableData) {
            return $value->toArray();
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => self::normalize($item), $value);
        }

        return $value;
    }
}
