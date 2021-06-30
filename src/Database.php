<?php

declare(strict_types=1);

namespace App;

use App\Exception\ConfigurationException;
use App\Exception\StorageException;
use App\Exception\NotFoundException;
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

    public function getNote(int $id): array
    {
        try {
            $query = "SELECT * FROM mynotes WHERE id = $id";
            $result = $this->conn->query($query);
            $note = $result->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            throw new StorageException('Cant get note',400);
        }

        if(!$note) {
            throw new NotFoundException("Note doeasn't exist");
        }
        return $note;
    }


    public function getNotes(int $pageNumber, int $pageSize, string $sortBy, string $sortOrder): array
    {
        try {
            $limit = $pageSize;
            $offset = ($pageNumber - 1) * $pageSize;

            if(!in_array($sortBy, ['created', 'title'])) {
                $sortBy = 'title';
            }
            if(!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $query = "SELECT id, title, created FROM mynotes ORDER BY $sortBy $sortOrder LIMIT $offset, $limit";
            $result = $this->conn->query($query);
            return $result->fetchAll(PDO::FETCH_ASSOC);;
        } catch(Throwable $exception) {
            throw new StorageException('Cant get notes', 400);
        }
    }

    public function getCount(): int
    {
        try {
            $query = "SELECT count(*) AS cn FROM mynotes";
            $result = $this->conn->query($query);
            $result =  $result->fetch(PDO::FETCH_ASSOC);
            if($result === false) {
                throw new StorageException('Cant get information about how much notes u have',400);
            }
            return (int) $result['cn'];
        } catch(Throwable $exception) {
            throw new StorageException('Cant get information about how much notes u have', 400);
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

    public function editNote(int $id, array $data): void
    {
        try {
            $title = $this->conn->quote($data['title']);
            $description = $this->conn->quote($data['description']);

            $query = "UPDATE mynotes SET title = $title, description = $description WHERE id = $id";
            $this->conn->exec($query);
        } catch (Throwable $exception) {
            throw new StorageException('Cant update note', 400);
        }
    }

    public function deleteNote(int $id): void
    {
        try {
            $query = "DELETE FROM mynotes WHERE id=$id LIMIT 1";
            $this->conn->exec($query);
        } catch (Throwable $exception) {
            throw new StorageException('Cant delete note',400);
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