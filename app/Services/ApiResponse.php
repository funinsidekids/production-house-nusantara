<?php

namespace App\Services;

/**
 * Standard API response envelope used by every Learning OS endpoint.
 *
 * Success: { success, message, data, meta }
 * Error:   { success, message, errors }
 */
trait ApiResponse
{
    protected function ok(mixed $data = [], string $message = 'Success', array $meta = [], int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta ?: (object) [],
        ], $status);
    }

    protected function created(mixed $data = [], string $message = 'Created'): \Illuminate\Http\JsonResponse
    {
        return $this->ok($data, $message, [], 201);
    }

    protected function fail(string $message, int $status = 400, array $errors = []): \Illuminate\Http\JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /**
     * Convert a Laravel paginator into the standard meta block.
     */
    protected function paginationMeta(\Illuminate\Contracts\Pagination\Paginator $pager): array
    {
        return [
            'page' => $pager->currentPage(),
            'per_page' => $pager->perPage(),
            'total' => $pager->total(),
            'last_page' => $pager->lastPage(),
            'has_more' => $pager->hasMorePages(),
        ];
    }
}
