<?php

namespace App\Controllers\Api;

use App\Controllers\Api\AbstractApiController;
use App\Core\Database;
use App\Models\Task;
use DateTime;
// Import Session model

class TaskApiController extends AbstractApiController
{


    public function index()
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $taskModel = new Task(Database::getInstance());

        // muestra solo lo creado por el usario
        $tasks = $taskModel->findbyField('user_id', $user_id);
        echo json_encode($tasks);
    }

    public function show($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);

        if (empty($task) || $task[0]->user_id !== $user_id) { // Check ownership
            http_response_code(404);
            echo json_encode(['message' => 'Task not found or unauthorized']);
            return;
        }
        echo json_encode($task[0]);
    }

    public function store()
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Invalid JSON or empty request body']);
            return;
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
            http_response_code(400);
            echo json_encode(['message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        if ($task->saveRecord()) {
            http_response_code(201);
            echo json_encode($task->toArray());
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create task']);
        }
    }

    public function update($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);

        if (empty($task) || $task[0]->user_id !== $user_id) { 
            http_response_code(404);
            echo json_encode(['message' => 'Task not found or unauthorized']);
            return;
        }
        $task = $task[0];

        $task->title = $data['title'] ?? $task->title;
        $task->description = $data['description'] ?? $task->description;
        $task->status = $data['status'] ?? $task->status;
        $task->updated_at = (new DateTime())->format('Y-m-d H:i:s');

        if ($task->saveRecord()) {
            echo json_encode($task->toArray());
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update task']);
        }
    }

    public function delete($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);

        if (empty($task) || $task[0]->user_id !== $user_id) { 
            http_response_code(404);
            echo json_encode(['message' => 'Task not found or unauthorized']);
            return;
        }

        if ($taskModel->delete($taskModel->getmetadata(), $id)) {
            echo json_encode(['message' => 'Task deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete task']);
        }
    }
}
