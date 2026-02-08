<?php
declare(strict_types=1);

namespace App\Models;

class File
{
    /**
     * Get files from a specific directory.
     */
    public static function getFiles(int $userId, ?int $parentId): array
    {
        $sql = "SELECT id, file_name as name, upload_date as created_at, file_size as size FROM files WHERE user_id = :user_id AND " . 
               ($parentId ? "directory_id = :parent_id" : "directory_id IS NULL");

        $params = ['user_id' => $userId];
        if ($parentId) {
            $params['parent_id'] = $parentId;
        }

        return Database::fetchAll($sql, $params);
    }

    /**
     * Find a file by its ID.
     */
    public static function findById(int $fileId, int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM files WHERE id = :id AND user_id = :user_id',
            ['id' => $fileId, 'user_id' => $userId]
        );
    }

    /**
     * Create a new file record.
     */
    public static function create(int $userId, ?int $parentId, string $originalName, string $storedName, string $mimeType, int $size): bool
    {
        return Database::insert('files', [
            'user_id' => $userId,
            'directory_id' => $parentId,
            'file_name' => $originalName,
            'file_path' => $storedName,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'upload_date' => date('Y-m-d H:i:s')
        ]) !== null;
    }

    /**
     * Rename a file.
     */
    public static function rename(int $fileId, int $userId, string $newName): bool
    {
        return Database::update('files', ['file_name' => $newName], ['id' => $fileId, 'user_id' => $userId]) > 0;
    }

    /**
     * Delete a file.
     */
    public static function delete(int $fileId, int $userId): bool
    {
        return Database::delete('files', ['id' => $fileId, 'user_id' => $userId]) > 0;
    }
}
