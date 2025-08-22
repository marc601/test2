<?php

namespace App\Controllers\Api;

use App\Controllers\Api\AbstractApiController;
use App\Core\JsonResponse;
use App\Core\Database;
use App\Models\Task;
use App\Models\Session;
use DateTime;


class TaskApiController extends AbstractApiController
{


    public function index()
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());

        $tasks = $taskModel->findbyField('user_id', $user_id);
        JsonResponse::ok($tasks);
    }

    public function show($id)
    {
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);
        $user_id = $this->authenticate();

        if (empty($task) || $task[0]->user_id !== $user_id) { // Check ownership
            return JsonResponse::notFound('Task not found or unauthorized');
        }
        JsonResponse::ok($task[0]->toArray());
    }

    public function store()
    {
        $user_id = $this->authenticate();
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            return JsonResponse::send(400, ['message' => 'Invalid JSON or empty request body']);
        }

        $errors = [];



        $now = new DateTime();
        $task = new Task(Database::getInstance());
        $task->user_id = $user_id;
        $task->title = isset($data['title']) ? trim($data['title']) : null;
        $task->description = isset($data['description']) ? trim($data['description']) : null;
        $task->status = $data['status'] ?? null;
        $task->created_at = $now->format('Y-m-d H:i:s');
        $task->updated_at = $now->format('Y-m-d H:i:s');
        $errors = $task->validate();
        if (!empty($errors)) {
            return JsonResponse::unprocessable($errors);
        }
        if ($task->saveRecord()) {
            JsonResponse::created($task->toArray());
        } else {
            JsonResponse::serverError('Failed to create task');
        }
    }

    public function update($id)
    {
        $user_id = $this->authenticate();
        $data = json_decode(file_get_contents('php://input'), true);

        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);

        if (empty($task) || $task[0]->user_id !== $user_id) {
            return JsonResponse::notFound('Task not found or unauthorized');
        }
        $task = $task[0];

        $task->title = $data['title'] ?? $task->title;
        $task->description = $data['description'] ?? $task->description;
        $task->status = $data['status'] ?? $task->status;
        $task->updated_at = (new DateTime())->format('Y-m-d H:i:s');

        $errors = $task->validate();
        if (!empty($errors)) {
            return JsonResponse::unprocessable($errors);
        }

        if ($task->saveRecord()) {
            JsonResponse::ok($task->toArray());
        } else {
            JsonResponse::serverError('Failed to update task');
        }
    }

    public function delete($id)
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);

        if (empty($task) || $task[0]->user_id !== $user_id) {
            return JsonResponse::notFound('Task not found or unauthorized');
        }

        if ($taskModel->delete($taskModel->getmetadata(), $id)) {
            JsonResponse::ok(['message' => 'Task deleted']);
        } else {
            JsonResponse::serverError('Failed to delete task');
        }
    }
}
