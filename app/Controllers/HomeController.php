<?php

namespace App\Controllers;
use App\Core\Database;
use App\Models\Session;
use App\Models\User;

use App\Controllers\AbstractController;

class HomeController
{
    public function index()
    {
        $data = [
            'title' => 'Login'
        ];
        require_once __DIR__ . '/../Views/home.php';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            header('Location: /');
            exit;
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->findbyField('email', $email);

        if (empty($user) || !password_verify($password, $user[0]->password)) {
            header('Location: /');
            exit;
        }

        $user = $user[0];

        $session = new Session(Database::getInstance());
        $session->user_id = $user->id;
        $session->session_token = bin2hex(random_bytes(12)); // 24 characters
        $session->cookie_token = null;
        $session->expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if ($session->saveRecord()) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['token'] = $session->session_token;
            $_SESSION['userName'] = $user->name;
            header('Location: /task/index');
        } else {
            header('Location: /?error=session_creation_failed');
            exit;
        }
    }

    public function logout()
    {
        if (isset($_SESSION['token'])) {
            $sessionModel = new Session(Database::getInstance());
            $session = $sessionModel->findbyField('session_token', $_SESSION['token']);

            if (!empty($session)) {
                $session = $session[0];
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("DELETE FROM sessions WHERE id = :id");
                $stmt->execute(['id' => $session->id]);
            }
        }
        session_destroy();
        header('Location: /');
        exit;
    }
}
