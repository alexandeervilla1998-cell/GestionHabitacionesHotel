<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseController
{
    /**
     * Send a success response
     */
    protected function sendResponse($result, $message = 'Operación exitosa', $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result
        ];

        return response()->json($response, $code);
    }

    /**
     * Send an error response
     */
    protected function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a paginated response
     */
    protected function sendPaginatedResponse(LengthAwarePaginator $paginator, $message = 'Datos obtenidos correctamente'): JsonResponse
    {
        $data = [
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ]
        ];

        return $this->sendResponse($data, $message);
    }

    /**
     * Send validation error response
     */
    protected function sendValidationError($error, $errorMessages = []): JsonResponse
    {
        return $this->sendError($error, $errorMessages, 422);
    }

    /**
     * Send unauthorized response
     */
    protected function sendUnauthorizedError($message = 'No autorizado'): JsonResponse
    {
        return $this->sendError($message, [], 401);
    }

    /**
     * Send forbidden response
     */
    protected function sendForbiddenError($message = 'Acceso prohibido'): JsonResponse
    {
        return $this->sendError($message, [], 403);
    }

    /**
     * Send not found response
     */
    protected function sendNotFound($message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->sendError($message, [], 404);
    }

    /**
     * Send server error response
     */
    protected function sendServerError($message = 'Error del servidor'): JsonResponse
    {
        return $this->sendError($message, [], 500);
    }
}
