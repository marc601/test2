<?php

namespace App\Controllers;
use App\Class\Task;
use App\Class\TaskManager;

class TestOneController
{
    public function index()
    {
        $taskManager = new TaskManager();

        $data = [
            'title' => 'Organizador de Tareas',
            'message' => 'Peibo (Test 1)',
            'taskManager' => $taskManager,
            'statuses' => Task::$statuses
        ];
        require_once __DIR__ . '/../Views/testOne.php';
    }

    public function addTask()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tittle = filter_input(INPUT_POST, 'tittle');
            $description = filter_input(INPUT_POST, 'description');
            $status = filter_input(INPUT_POST, 'status');

            if ($tittle && $description && $status !== false) {
                $task = new Task();
                $task->setTittle($tittle);
                $task->setDescription($description);
                $task->setStatus((int) $status);

                $taskManager = new TaskManager();
                $taskManager->addTask($task);
            }
        }
        header('Location: /testOne');
        exit();
    }

    public function updateTaskStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $taskManager = new TaskManager();
            $id = filter_input(INPUT_POST, 'id');
            $action = filter_input(INPUT_POST, 'action');

            if ($id && $action) {
                $task = $taskManager->getTaskById((int) $id);
                if ($task) {
                    if ($action == Task::STATUS_DONE) {
                        $task->markDone();
                    } elseif ($action == Task::STATUS_IN_PROGRESS) {
                        $task->markInProgress();
                    }
                    // Re-add the task to update it in the session
                    $taskManager->addTask($task);
                }
            }
        }
        header('Location: /testOne');
        exit();
    }
}
