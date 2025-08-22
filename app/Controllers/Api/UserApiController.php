<?php

namespace App\Controllers\Api;

use App\Core\JsonResponse;
use App\Core\Database;
use App\Models\User;
use App\Controllers\Api\AbstractApiController;
use DateTime;

class UserApiController extends AbstractApiController
{
    public function index(?User $user = null)
    {
        $user_id = $this->authenticate();

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

        JsonResponse::ok($usersData);
    }

    public function show($id, ?User $user = null)
    {
        $user_id = $this->authenticate();
        $userModel = $user ?? new User(Database::getInstance());
        $user = $userModel->find($id);

        if ($user) {
            $userData = $user[0]->toArray();
            unset($userData['password']);
            JsonResponse::ok($userData);
        } else {
            JsonResponse::notFound('User not found');
        }
    }

    public function store(?User $user = null)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            return JsonResponse::send(400, ['message' => 'Invalid JSON or empty request body']);
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
            return JsonResponse::unprocessable($errors);
        }

        if ($user->saveRecord()) {
            $userData = $user->toArray();
            unset($userData['password']);
            JsonResponse::created($userData);
        } else {
            JsonResponse::serverError('Failed to create user');
        }
    }

    public function update($id)
    {
        $user_id = $this->authenticate();

        if ($user_id != $id) {
            return JsonResponse::send(403, ['message' => 'Forbidden: You can only update your own profile.']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            return JsonResponse::send(400, ['message' => 'Invalid JSON or empty request body']);
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);
        if (!$user) {
            return JsonResponse::notFound('User not found');
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
            return JsonResponse::unprocessable($errors);
        }


        if ($user->saveRecord()) {
            $userData = $user->toArray();
            unset($userData['password']);
            JsonResponse::ok($userData);
        } else {
            JsonResponse::serverError('Failed to update user');
        }
    }

    public function delete($id)
    {
        $user_id = $this->authenticate();

        if ($user_id != $id) {
            return JsonResponse::send(403, ['message' => 'Forbidden: You can only delete your own profile.']);
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);

        if (!$user) {
            return JsonResponse::notFound('User not found');
        }

        if ($userModel->deleteRecord($id)) {
            JsonResponse::ok(['message' => 'User deleted']);
        } else {
            JsonResponse::serverError('Failed to delete user');
        }
    }
}
