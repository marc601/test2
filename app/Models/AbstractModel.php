<?php

declare(strict_types=1);

namespace App\Models;

use App\core\Database;
use PDO;


abstract class AbstractModel
{

    private ?Database $db = null;

    public function __construct(?Database $db = null)
    {
        $this->db = $db;
    }

    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return User|false
     */
    public function select($metadata, $model, $id = null)
    {
        $conn = $this->db->getConnection();
        $where = $id ? "WHERE id = :id" : "WHERE 1";
        $stmt = $conn->prepare("SELECT * FROM {$metadata["tableName"]} {$where}");
        if ($id) {
            $stmt->execute(['id' => $id]);
            $data[] = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $result = [];
        if ($data) {
            foreach ($data as $value) {
                if ($value) {
                    $result[] = $model::fromArray($value);
                }
            }
        }
        return $result;
    }

    public function selectById($metadata, $model, $id)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM {$metadata['tableName']} WHERE {$metadata['primaryKey']} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $model::fromArray($data);
        }

        return null;
    }


    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|false
     */
    public function selectByField($metadata, $model, $field, $value)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM {$metadata['tableName']} WHERE {$field} = :value");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];

        if ($data) {
            foreach ($data as $value) {
                $result[] = $model::fromArray($value);
            }
        }

        return $result;
    }

    /**
     * Save a new user to the database.
     *
     * @return bool
     */
    public function save($metadata, $model)
    {
        $conn = $this->db->getConnection();
        $columns = array_filter($metadata['columns'], function ($column) use ($metadata) {
            return $column !== $metadata['primaryKey'];
        });

        $arrayModel = $model->toArray();
        $params = [];

        // If the model has a primary key, it's an update
        if (isset($model->{$metadata['primaryKey']}) && !empty($model->{$metadata['primaryKey']})) {
            $fields = implode(', ', array_map(fn($c) => "$c = :$c", $columns));
            $stmt = $conn->prepare(
                "UPDATE {$metadata['tableName']} SET {$fields} WHERE {$metadata['primaryKey']} = :{$metadata['primaryKey']}"
            );
            $params[':' . $metadata['primaryKey']] = $arrayModel[$metadata['primaryKey']];
        } else { // Otherwise, it's an insert
            $fields = implode(', ', $columns);
            $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
            $stmt = $conn->prepare(
                "INSERT INTO {$metadata['tableName']} ({$fields}) VALUES ({$placeholders})"
            );
        }

        foreach ($columns as $column) {
            $params[':' . $column] = $arrayModel[$column] ?? null;
        }

        if ($stmt->execute($params)) {
            if (!isset($model->{$metadata['primaryKey']})) {
                $model->{$metadata['primaryKey']} = $conn->lastInsertId();
            }
            return true;
        }
        return false;
    }

    public function delete($metadata, $id)
    {

        $stmt = $this->db->getConnection()->prepare(
            "DELETE FROM {$metadata['tableName']} WHERE {$metadata['primaryKey']} = :id"
        );
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    //for validation fields
    abstract function validate();
}
