<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

class File
{
    public static function getDirectoryContents(int $userId, ?int $directoryId = null): array
    {
        $pdo = Database::getConnection();

        $directoriesQuery = $pdo->prepare(
            'SELECT id, name, created_at FROM directories WHERE user_id = :user_id AND parent_id <=> :parent_id'
        );
        $directoriesQuery->execute([':user_id' => $userId, ':parent_id' => $directoryId]);
        $directories = $directoriesQuery->fetchAll(PDO::FETCH_ASSOC);

        $filesQuery = $pdo->prepare(
            'SELECT id, name, created_at FROM files WHERE user_id = :user_id AND directory_id <=> :parent_id'
        );
        $filesQuery->execute([':user_id' => $userId, ':parent_id' => $directoryId]);
        $files = $filesQuery->fetchAll(PDO::FETCH_ASSOC);

        $parentId = null;
        if ($directoryId) {
            $parentQuery = $pdo->prepare('SELECT parent_id FROM directories WHERE id = :id AND user_id = :user_id');
            $parentQuery->execute([':id' => $directoryId, ':user_id' => $userId]);
            $result = $parentQuery->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $parentId = $result['parent_id'];
            }
        }

        return [
            'directories' => $directories,
            'files' => $files,
            'parent_id' => $parentId,
        ];
    }

    public static function createDirectory(int $userId, string $name, ?int $parentId = null): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO directories (user_id, name, parent_id) VALUES (:user_id, :name, :parent_id)'
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':parent_id' => $parentId,
        ]);
    }

    public static function findDirectoryById(int $id, int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM directories WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $directory = $stmt->fetch(PDO::FETCH_ASSOC);
        return $directory ?: null;
    }

    public static function renameDirectory(int $id, int $userId, string $newName): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE directories SET name = :name WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':name' => $newName, ':id' => $id, ':user_id' => $userId]);
    }

    public static function deleteDirectory(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        // Additional logic needed here to handle sub-files and sub-directories
        $stmt = $pdo->prepare('DELETE FROM directories WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public static function createFile(int $userId, ?int $directoryId, string $originalName, string $storedName, string $mimeType, int $size): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO files (user_id, directory_id, name, stored_name, mime_type, size) VALUES (:user_id, :dir_id, :name, :stored_name, :mime_type, :size)'
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':dir_id' => $directoryId,
            ':name' => $originalName,
            ':stored_name' => $storedName,
            ':mime_type' => $mimeType,
            ':size' => $size,
        ]);
    }
    
    public static function findFileById(int $id, int $userId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function renameFile(int $id, int $userId, string $newName): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE files SET name = :name WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':name' => $newName, ':id' => $id, ':user_id' => $userId]);
    }

    public static function deleteFile(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        // You might want to delete the actual file from the server as well
        $stmt = $pdo->prepare('DELETE FROM files WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    // ... other methods for sharing etc.
}
