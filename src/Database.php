<?php

declare(strict_types=1);

namespace App;

require_once "Exception/StorageException.php";

use App\Exception\ConfigurationException;
use App\Exception\StorageException;
use PDO;
use PDOException;
use Throwable;

class Database
{
    private PDO $conn;

    public function __construct(array $config)
    {
        try {
            $this->validateConfig($config);
            $this->createConnection($config);
        } catch (PDOException $exception) {
            throw new StorageException('Connection error');
        }
    }

    public function createNote(array $data): void
    {
        try {
            $title = $this->conn->quote($data['title']);
            $description = $this->conn->quote($data['description']);
            $created = $this->conn->quote(date('Y-m-d H:i:s'));
            $query = "INSERT INTO mynotes(title,description,created) VALUES($title,$description,$created)";
            $this->conn->exec($query);
        } catch (Throwable $exception) {
            throw new StorageException("Cant create note", 400);
        }
    }

    private function createConnection(array $config): void
    {
        $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
        $this->conn = new PDO($dsn,$config['user'],$config['password'],[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    private function validateConfig(array $config): void
    {
        if(empty($config['database']) || empty($config['host']) || empty($config['user']) || empty($config['password'])) {
            throw new ConfigurationException('Storage configuration error');
        }
    }
}