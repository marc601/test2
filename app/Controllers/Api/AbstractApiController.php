<?php
namespace App\Controllers\Api;

use App\Models\Session;
use App\Core\Database;
abstract class AbstractApiController
{
    protected function authenticate()
    {
        // Para sesiones en version web (no implementado)
        $token = $_SESSION['token'] ?? null;

        // Para autorización con APIS
        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Token missing']);
            exit;
        }
        $sessionModel = new Session(Database::getInstance());
        $session = $sessionModel->findbyField('session_token', $token);

        if (empty($session) || strtotime($session[0]->expires_at) < time()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Invalid or expired token']);
            exit;
        }

        return $session[0]->user_id;
    }
}