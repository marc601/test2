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
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Invalid JSON or empty request body']);
            return;
        }

        $now = new DateTime();
        $userModel = new User(Database::getInstance());
        $user = $user ?? $userModel;
        $user->name = isset($data['name']) ? trim($data['name']) : null;
        $user->email = isset($data['email']) ? trim($data['email']) : null;
        $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->created_at = $now->format('Y-m-d H:i:s');
        $user->updated_at = $now->format('Y-m-d H:i:s');
        $errors = $user->validate();
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['message' => 'Validation failed', 'errors' => $errors]);
            return;
        }

        if ($user->saveRecord()) {
            http_response_code(201);
            $user->password = 'Secret';
            echo json_encode($user->toArray());
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create user']);
        }
    }

    public function update($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');

        if ($user_id != $id) {
            http_response_code(403); // Forbidden
            echo json_encode(['message' => 'Forbidden: You can only update your own profile.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON.']);
            return;
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            return;
        }
        $user = $user[0];

        $user->name = isset($data['name']) ? trim($data['name']) : $user->name;
        $user->email = isset($data['email']) ? trim($data['email']) : $user->email;
        if (!empty($data['password'])) {
            $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $user->updated_at = (new DateTime())->format('Y-m-d H:i:s');

        $errors = $user->validate(true);
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['message' => 'Validation failed', 'errors' => $errors]);
            return;
        }


        if ($user->saveRecord()) {
            $user->password = 'Secret';
            echo json_encode($user->toArray());
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update user']);
        }
    }

    public function delete($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');

        // --- MEJORA DE AUTORIZACIÓN ---
        if ($user_id != $id) {
            http_response_code(403); // Forbidden
            echo json_encode(['message' => 'Forbidden: You can only delete your own profile.']);
            return;
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            return;
        }

        if ($userModel->deleteRecord($id)) {
            echo json_encode(['message' => 'User deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete user']);
        }
    }
}
