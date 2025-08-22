<?php

namespace App\Class;

use App\Class\Task; // Ensure Task class is available

class TaskManager
{
    private array $tasks = [];
    private static int $nextId = 1;

    public function __construct()
    {
        // Ensure Task class is loaded before attempting to unserialize from session
        // This is less critical now that we're storing arrays, but good for consistency
//        class_exists('App\\Class\\Task');

        if (isset($_SESSION['tasks'])) {
            foreach ($_SESSION['tasks'] as $taskData) {
                // Reconstruct Task object from array data
                $task = Task::fromArray($taskData);
                $task->setId(self::$nextId);
                $this->tasks[] = $task;
                // Update nextId based on existing tasks
                if ($task->getId() >= self::$nextId) {
                    self::$nextId = $task->getId() + 1;
                }
            }
        }
    }

    public function addTask(Task $task): void
    {

        if (!$task->getId()) { // Assign ID only if not already set
            $task->setId(self::$nextId++);
            $this->tasks[] = $task;
        } else {
            foreach ($this->tasks as $t) {
                if ($t->getId() === $task->getId()) {
                    $t = $task;
                }
            }
        }


        // Store array representation of tasks in session
        $_SESSION['tasks'] = array_map(function ($t) {
            return $t->toArray();
        }, $this->tasks);
    }

    public function getAllTasks(): array
    {
        return $this->tasks;
    }

    public function getTaskById(int $id): ?Task
    {
        foreach ($this->tasks as $task) {
            if ($task->getId() === $id) {
                return $task;
            }
        }
        return null;
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->tasks as $task) {
            $array[] = $task->toArray();
        }

        return $array;

    }


}