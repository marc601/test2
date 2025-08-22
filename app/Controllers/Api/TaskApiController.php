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
        $now = new DateTime();

        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $task = new Task(Database::getInstance());

        if (empty($data)) {
            http_response_code(500);
            echo json_encode(['message' => 'data not fount']);
            exit;
        }
        $task->user_id = $user_id;
        $task->title = $data['title'];
        $task->description = $data['description'];
        $task->status = $data['status'];
        $task->created_at = $now->format('Y-m-d H:i:s');
        $task->updated_at = $now->format('Y-m-d H:i:s');



        if ($task->saveRecord()) {
            http_response_code(201);
            echo json_encode($task);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create task']);
            exit;
        }
    }

    public function update($id)
    {
        $user_id = $this->authenticate();
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);

        if (empty($task) || $task[0]->user_id !== $user_id) { // Check ownership
            http_response_code(404);
            echo json_encode(['message' => 'Task not found or unauthorized']);
            return;
        }
        $task = $task[0];

        $task->title = $data['title'] ?? $task->title;
        $task->description = $data['description'] ?? $task->description;
        $task->status = $data['status'] ?? $task->status;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tasks SET user_id = :user_id, title = :title, description = :description, status = :status WHERE id = :id");
        if (
            $stmt->execute([
                'user_id' => $task->user_id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'id' => $id
            ])
        ) {
            echo json_encode($task);
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

        if (empty($task) || $task[0]->user_id !== $user_id) { // Check ownership
            http_response_code(404);
            echo json_encode(['message' => 'Task not found or unauthorized']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            echo json_encode(['message' => 'Task deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete task']);
        }
    }
}