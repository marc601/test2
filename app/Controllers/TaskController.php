<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\Task;
use App\Models\User;
use App\Controllers\AbstractController;

class TaskController extends AbstractController
{
    public function index()
    {
        $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $tasks = $taskModel->findbyField('user_id', $_SESSION['user_id']);
        $data = [
            'title' => 'Tareas',
            'tasks' => $tasks,
            'statuses' => Task::$statuses
        ];
        require_once __DIR__ . '/../Views/tasks/index.php';
    }

    public function show($id)
    {
        $this->authenticate();
        $taskModel = new Task(Database::getInstance());

        $task = $taskModel->find($id);

        $data = [
            'title' => 'Detalle de la Tarea',
            'task' => $task[0],
            'statuses' => Task::$statuses
        ];
        require_once __DIR__ . '/../Views/tasks/show.php';
    }

    public function create()
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $users = $userModel->find();
        $data = [
            'title' => 'Nueva Tarea',
            'users' => $users,
            'statuses' => Task::$statuses
        ];
        require_once __DIR__ . '/../Views/tasks/create.php';
    }

    public function store()
    {
        $this->authenticate();
        $task = new Task(Database::getInstance());
        $task->user_id = $_SESSION['user_id'];
        $task->title = $_POST['title'];
        $task->description = $_POST['description'];
        $task->status = $_POST['status'];
        $task->saveRecord();
        header('Location: /task');
    }

    public function edit($id)
    {
        $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->find($id);
        $userModel = new User(Database::getInstance());
        $users = $userModel->find();
        $data = [
            'title' => 'Editar Tarea',
            'task' => $task[0],
            'users' => $users,
            'statuses' => Task::$statuses
        ];
        require_once __DIR__ . '/../Views/tasks/edit.php';
    }

    public function update($id)
    {
        $this->authenticate();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tasks SET user_id = :user_id, title = :title, description = :description, status = :status WHERE id = :id");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'status' => $_POST['status'],
            'id' => $id
        ]);

        header('Location: /task');
    }

    public function delete($id)
    {
        $this->authenticate();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header('Location: /task');
    }

    public function start($id)
    {
        $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);
        $task->markInProgress();

        header('Location: /task');
        exit;
    }

    public function finish($id)
    {
        $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);
        $task->markDone();
        header('Location: /task');
        exit;
    }
}
