<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait ApiResponseTrait
{
    /**
     * Return a successful response
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message = 'Error', int $statusCode = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        ?string $resourceClass = null,
        array $meta = [],
        string $message = 'Success'
    ): JsonResponse {
        $data = $resourceClass 
            ? $resourceClass::collection($paginator->items())
            : $paginator->items();

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'path' => $paginator->path(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ], $meta)
        ];

        return response()->json($response);
    }

    /**
     * Return a collection response
     */
    protected function collectionResponse(
        Collection $collection,
        ?string $resourceClass = null,
        array $meta = [],
        string $message = 'Success'
    ): JsonResponse {
        $data = $resourceClass 
            ? $resourceClass::collection($collection)
            : $collection;

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Return a resource response
     */
    protected function resourceResponse(
        $resource,
        ?string $resourceClass = null,
        array $meta = [],
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $data = $resourceClass ? new $resourceClass($resource) : $resource;

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a created response
     */
    protected function createdResponse($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return a deleted response
     */
    protected function deletedResponse(string $message = 'Deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message);
    }

    /**
     * Return a not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return a forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }
}