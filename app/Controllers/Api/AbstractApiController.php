<?php
namespace App\Controllers\Api;

use App\Models\Session;
use App\Core\Database;
abstract class AbstractApiController
{
    protected function authenticate()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Token missing']);
            exit;
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Malformed token']);
            exit;
        }

        $jwtHandler = new \App\Core\JwtHandler();
        $data = $jwtHandler->decode($jwt);

        if (!$data) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Invalid or expired token']);
            exit;
        }

        return $data['id'];
    }
}