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
    public $password;
    public $created_at;
    public $updated_at;

    public function __construct(?Database $connection)
    {
        parent::__construct($connection);
        $this->conn = $connection;
    }

    protected function getmetadata()
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
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        return $this->save($this->getmetadata(), $this);
    }


    /**
     * Update the user in the database.
     *
     * @return bool
     */
    public function update()
    {
        $stmt = $this->conn->prepare(
            "UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id"
        );

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Delete the user from the database.
     *
     * @return bool
     */
    public function delete()
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
