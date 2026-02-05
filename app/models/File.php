<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

class File
{
    // ... (no changes to getDirectoryContents for now, as the error is elsewhere and this part is complex)
    public static function getDirectoryContents(int $userId, ?int $directoryId = null): array
    {
        $pdo = Database::getConnection();

        $directoriesQuery = $pdo->prepare(
            'SELECT id, name, created_at FROM directories WHERE user_id = :user_id AND parent_id IS NOT DISTINCT FROM :parent_id'
        );
        $directoriesQuery->execute([':user_id' => $userId, ':parent_id' => $directoryId]);
        $directories = $directoriesQuery->fetchAll(PDO::FETCH_ASSOC);

        // --- FIX: Corrected column names based on the actual database schema ---
        // 'file_name' is the correct column for the file's name.
        // 'upload_date' is the correct column for the creation timestamp.
        $filesQuery = $pdo->prepare(
            'SELECT id, file_name as name, upload_date as created_at FROM files WHERE user_id = :user_id /* AND directory_id IS NOT DISTINCT FROM :parent_id */'
        );
        // The `directory_id` column does not exist in the `files` table, so the condition is commented out.
        // This is a temporary fix to prevent a crash. The schema itself seems to be missing the directory relationship for files.
        $filesQuery->execute([':user_id' => $userId/*, ':parent_id' => $directoryId*/]);
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
        $stmt = $pdo->prepare('DELETE FROM directories WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    // --- FIX: The entire method has been updated to match the correct database schema ---
    public static function createFile(int $userId, ?int $directoryId, string $originalName, string $storedName, string $mimeType, int $size): bool
    {
        $pdo = Database::getConnection();
        // Corrected column names: file_name, file_path, file_size.
        // Removed non-existent `directory_id` from this query.
        $stmt = $pdo->prepare(
            'INSERT INTO files (user_id, file_name, file_path, file_size, mime_type) VALUES (:user_id, :file_name, :file_path, :file_size, :mime_type)'
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':file_name' => $originalName, // $originalName maps to file_name
            ':file_path' => $storedName,   // $storedName maps to file_path
            ':file_size' => $size,         // $size maps to file_size
            ':mime_type' => $mimeType,
        ]);
    }
    
    public static function findFileById(int $id, int $userId)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- FIX: Corrected column name from 'name' to 'file_name' ---
    public static function renameFile(int $id, int $userId, string $newName): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE files SET file_name = :name WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':name' => $newName, ':id' => $id, ':user_id' => $userId]);
    }

    public static function deleteFile(int $id, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM files WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
}
