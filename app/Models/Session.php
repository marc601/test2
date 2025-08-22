<?php

namespace App\Models;

use App\Core\Database;

class Session extends AbstractModel
{
    public $id;
    public $user_id;
    public $session_token;
    public $cookie_token;
    public $expires_at;

    public function __construct(?Database $connection)
    {
        parent::__construct($connection);
    }

    protected function getmetadata()
    {
        return [
            'tableName' => 'sessions',
            'primaryKey' => 'id',
            'columns' => [
                'id',
                'user_id',
                'session_token',
                'cookie_token',
                'expires_at'
            ]
        ];
    }

    public static function fromArray(array $data): self
    {
        $session = new self(Database::getInstance());
        $session->id = $data['id'];
        $session->user_id = $data['user_id'];
        $session->session_token = $data['session_token'];
        $session->cookie_token = $data['cookie_token'];
        $session->expires_at = $data['expires_at'];
        return $session;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'session_token' => $this->session_token,
            'cookie_token' => $this->cookie_token,
            'expires_at' => $this->expires_at
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
}
