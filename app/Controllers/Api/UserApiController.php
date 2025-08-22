<?php

namespace App\Controllers\Api;

use App\Core\Database;
use App\Models\User;
use App\Controllers\Api\AbstractApiController;
use DateTime;

class UserApiController extends AbstractApiController
{
    public function index(?User $user = null)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');

        $userModel = $user ?? new User(Database::getInstance());
        $users = $userModel->find();

        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];
        }

        echo json_encode($usersData);
    }

    public function show($id, ?User $user = null)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $userModel = $user ?? new User(Database::getInstance());
        $user = $userModel->find($id);

        if ($user) {
            unset($user->password);
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    }

    public function store(?User $user = null)
    {
        $now = new DateTime();

        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $user = $user ?? new User(Database::getInstance());
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->created_at = $now->format('Y-m-d H:i:s');
        $user->updated_at = $now->format('Y-m-d H:i:s');

        if ($user->saveRecord()) {
            http_response_code(201);
            $user->password = 'Secret';
            echo json_encode($user);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create user']);
        }
    }

    public function update($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id)[0];

        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            return;
        }

        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;

        $password = $user->password;
        if (!empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
        if (
            $stmt->execute([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
                'id' => $id
            ])
        ) {
            echo json_encode($user);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update user']);
        }
    }

    public function delete($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            echo json_encode(['message' => 'User deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete user']);
        }
    }
}
