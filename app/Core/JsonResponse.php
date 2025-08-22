<?php

namespace App\Core;

/**
 * A utility class for sending standardized JSON responses.
 */
class JsonResponse
{
    /**
     * Sends a JSON response with a given status code and data.
     *
     * @param int $statusCode The HTTP status code.
     * @param array $data The data to be encoded as JSON.
     * @param array $headers Additional headers to send.
     */
    public static function send(int $statusCode, array $data = [], array $headers = []): void
    {
        // Prevent further output
        if (headers_sent()) {
            return;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }

        // Don't send a body for 204 No Content
        if ($statusCode === 204) {
            return;
        }

        echo json_encode($data);
    }

    public static function ok($data = []): void
    {
        self::send(200, $data);
    }

    public static function created(array $data = []): void
    {
        self::send(201, $data);
    }

    public static function notFound(string $message = 'Resource not found'): void
    {
        self::send(404, ['message' => $message]);
    }

    public static function unprocessable(array $errors): void
    {
        self::send(422, ['message' => 'Validation failed', 'errors' => $errors]);
    }

    public static function serverError(string $message = 'Internal Server Error'): void
    {
        self::send(500, ['message' => $message]);
    }
}
