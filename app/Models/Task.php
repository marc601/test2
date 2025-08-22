<?php

namespace App\Models;

use App\Core\Database;

class Task extends AbstractModel
{
    public $id;
    public $user_id;
    public $title;
    public $description;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(?Database $connection)
    {
        parent::__construct($connection);
    }

    public function getmetadata()
    {
        return [
            'tableName' => 'tasks',
            'primaryKey' => 'id',
            'columns' => [
                'id',
                'user_id',
                'title',
                'description',
                'status',
                'created_at',
                'updated_at'
            ]
        ];
    }

    const STATUS_PENDING = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_DONE = 3;

    public static array $statuses = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_IN_PROGRESS => 'En progreso',
        self::STATUS_DONE => 'Realizado'
    ];

    public function markInProgress(): void
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->updateStatus();
    }
    public function markDone(): void
    {
        $this->status = self::STATUS_DONE;
        $this->updateStatus();
    }

    private function updateStatus(): void
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tasks SET status = :status WHERE id = :id");
        $stmt->execute([
            'status' => $this->status,
            'id' => $this->id
        ]);
    }

    public static function fromArray(array $data): self
    {
        $task = new self(Database::getInstance());
        $task->id = $data['id'];
        $task->user_id = $data['user_id'];
        $task->title = $data['title'];
        $task->description = $data['description'];
        $task->status = $data['status'];
        $task->created_at = $data['created_at'];
        $task->updated_at = $data['updated_at'];
        return $task;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    public function find(?int $id = null)
    {
        return $this->select($this->getmetadata(), $this, $id);
    }

    public function findbyField($field, $value)
    {
        return $this->selectbyField($this->getmetadata(), $this, $field, $value);
    }

    public function saveRecord()
    {
        return $this->save($this->getmetadata(), $this);
    }

    public function validate()
    {
        $errors = [];
        if (!$this->title || !is_string($this->title)) {
            $errors['title'] = 'The title field is required and must be a string.';
        }
        if (!$this->status || !is_int($this->status)) {
            $errors['status'] = 'The status field is required and must be an integer.';
        }
        return $errors;
    }
}
