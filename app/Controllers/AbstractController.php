<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Session; // Import Session model

abstract class AbstractController
{
    abstract public function index();
    abstract public function create();
    abstract public function edit(int $id);
    abstract public function delete(int $id);


    protected function authenticate()
    {

        $token = $_SESSION['token'] ?? null;
        if (!$token) {
            header('Location: /');
            exit;
        }

        $sessionModel = new Session(Database::getInstance());
        $session = $sessionModel->findbyField('session_token', $token);
        if (empty($session) || strtotime($session[0]->expires_at) < time()) {
            session_destroy(); // Destroy invalid session
            header('Location: /');
            exit;
        }
        return $session[0]->user_id;
    }

    protected function render(string $view, array $data = [])
    {
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "Error: View '{$view}' not found.";
        }
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
