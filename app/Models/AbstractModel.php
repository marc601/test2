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
        // podriamos pasar la estatica de la clase Database
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
        $columns = array_filter($metadata['columns'], function ($column) use ($metadata) {
            return $column !== $metadata['primaryKey'];
        });
        $fields = implode(', ', $columns);
        $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO {$metadata['tableName']} ({$fields}) VALUES ({$placeholders})"
        );
        $arrayModel = $model->toArray();
        foreach ($columns as $column) {
            $stmt->bindParam(':' . $column, $arrayModel[$column]);
        }

        if ($stmt->execute()) {
            $model->{$metadata['primaryKey']} = $this->db->getConnection()->lastInsertId();
            return true;
        }
        return false;
    }
}
