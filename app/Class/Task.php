<?php

namespace App\Class;

use Exception;

class Task
{
    private ?int $id = null;
    private ?string $tittle = null;
    private string $description;
    private ?int $status = null;

    const STATUS_PENDING = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_DONE = 3;

    public static array $statuses = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_IN_PROGRESS => 'En progreso',
        self::STATUS_DONE => 'Realizado'
    ];
    public function __toString(): string
    {
        return $this->description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTittle(): ?string
    {
        return $this->tittle;
    }

    public function setTittle(?string $tittle): void
    {
        $this->tittle = $tittle;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->validataStatus($status);
        $this->status = $status;
    }

    protected function validataStatus(?int $status)
    {

        if (!$status) {
            throw new Exception("Indique almenos un estatus");
        }
        if (!in_array($status, array_keys(self::$statuses))) {
            throw new Exception("El estatus : " . $status . " es invalido");
        }
    }
    public function markInProgress(): void
    {
        $this->setStatus(self::STATUS_IN_PROGRESS);
    }
    public function markDone(): void
    {
        $this->setStatus(self::STATUS_DONE);
    }

    public function getStatusString(): string
    {
        $statusString = '';
        $this->validataStatus($this->getStatus());

        if ($this->getStatus()) {
            $statusString = self::$statuses[$this->getStatus()];
        }
        return $statusString;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tittle' => $this->tittle,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }

    public static function fromArray(array $data): self
    {
        $task = new self();
        $task->setId($data['id'] ?? 0);
        $task->setTittle($data['tittle'] ?? null);
        $task->setDescription($data['description'] ?? '');
        $task->setStatus($data['status'] ?? self::STATUS_PENDING);
        return $task;
    }
}
