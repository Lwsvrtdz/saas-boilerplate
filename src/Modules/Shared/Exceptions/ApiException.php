<?php

namespace Modules\Shared\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(
        string $message,
        protected int $status = 400,
        protected array $errors = [],
    ) {
        parent::__construct($message, $status);
    }

    public static function unauthorized(string $message = 'Unauthorized.'): self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden.'): self
    {
        return new self($message, 403);
    }

    public static function notFound(string $message = 'Resource not found.'): self
    {
        return new self($message, 404);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->status);
    }
}
