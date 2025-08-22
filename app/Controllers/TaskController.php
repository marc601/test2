<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\Task;
use DateTime;

class TaskController extends AbstractController
{
    public function index()
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());

        // Use the model to find tasks for the logged-in user
        $tasks = $taskModel->findbyField('user_id', $user_id);

        $data = [
            'title' => 'Mis Tareas',
            'tasks' => $tasks,
            'statuses' => $taskModel::$statuses
        ];
        $this->render('tasks/index', $data);
    }

    public function create()
    {
        $this->authenticate();
        $data = [
            'title' => 'Crear Tarea',
            'task' => new Task(null),
            'statuses' => Task::$statuses,
            'errors' => []
        ];
        $this->render('tasks/create', $data);
    }

    public function store()
    {
        $user_id = $this->authenticate();
        $now = new DateTime();

        $task = new Task(Database::getInstance());
        $task->user_id = $user_id;
        $task->title = trim($_POST['title'] ?? '');
        $task->description = trim($_POST['description'] ?? '');
        $task->status = (int)($_POST['status'] ?? 0);
        $task->created_at = $now->format('Y-m-d H:i:s');
        $task->updated_at = $now->format('Y-m-d H:i:s');

        $errors = $task->validate();

        if (empty($errors)) {
            $task->saveRecord();
            $this->redirect('/task');
        }

        // If validation fails, re-render the form with errors and old data
        $data = [
            'title' => 'Crear Tarea',
            'task' => $task,
            'errors' => $errors
        ];
        $this->render('tasks/create', $data);
    }

    public function edit($id)
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);

        // Authorization: Check if the task exists and belongs to the user
        if (!$task || $task->user_id != $user_id) {
            $this->redirect('/task');
        }

        $data = [
            'title' => 'Editar Tarea',
            'task' => $task,
            'statuses' => Task::$statuses,
            'errors' => []
        ];
        $this->render('tasks/edit', $data);
    }

    public function update($id)
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);

        // Authorization: Check if the task exists and belongs to the user
        if (!$task || $task->user_id != $user_id) {
            $this->redirect('/task');
        }

        // Map and validate data
        $task->title = trim($_POST['title'] ?? '');
        $task->description = trim($_POST['description'] ?? '');
        $task->status = (int)($_POST['status'] ?? 0);
        $task->updated_at = (new DateTime())->format('Y-m-d H:i:s');

        $errors = $task->validate();

        if (empty($errors)) {
            $task->saveRecord();
            $this->redirect('/task');
        }

        // If validation fails, re-render the edit form
        $data = [
            'title' => 'Editar Tarea',
            'task' => $task,
            'errors' => $errors
        ];
        $this->render('tasks/edit', $data);
    }

    public function delete($id)
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);

        // Authorization: Check if the task exists and belongs to the user
        if (!$task || $task->user_id != $user_id) {
            $this->redirect('/task');
        }

        $taskModel->delete($taskModel->getmetadata(), $id);

        $this->redirect('/task');
    }
    public function start($id)
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);

        if (!$task || $task->user_id != $user_id) {
            $this->redirect('/task');
        }

        $task->markInProgress();
        $this->redirect('/task');
    }

    public function finish($id)
    {
        $user_id = $this->authenticate();
        $taskModel = new Task(Database::getInstance());
        $task = $taskModel->findById($id);

        if (!$task || $task->user_id != $user_id) {
            $this->redirect('/task');
        }

        $task->markDone();
        $this->redirect('/task');
    }
}
