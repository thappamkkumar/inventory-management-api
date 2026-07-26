<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(
        string $message = 'Success',
        mixed $data = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function created(
        string $message = 'Created successfully',
        mixed $data = null
    ): JsonResponse {
        return $this->success($message, $data, 201);
    }

    protected function error(
        string $message = 'Something went wrong',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}