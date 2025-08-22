<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

class User extends AbstractModel
{
    private $conn;

    public $id;
    public $name;
    public $email;
    public $password = 'secret';
    public $created_at;
    public $updated_at;

    public function __construct(?Database $connection)
    {
        parent::__construct($connection);
        $this->conn = $connection;
    }

    public function getmetadata()
    {
        return [
            'tableName' => 'users',
            'primaryKey' => 'id',
            'columns' => [
                'id',
                'name',
                'email',
                'password',
                'created_at',
                'updated_at'

            ]
        ];
    }

    public static function fromArray(?array $data): self
    {
        $user = new self(Database::getInstance());
        $user->id = $data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];
        return $user;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
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
        // The password should be hashed in the controller before calling this method.
        return $this->save($this->getmetadata(), $this);
    }

    public function deleteRecord($id)
    {
        return $this->delete($this->getmetadata(), $id);
    }
}
