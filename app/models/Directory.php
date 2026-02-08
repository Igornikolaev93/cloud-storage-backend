<?php
declare(strict_types=1);

namespace App\Models;

use Exception;

class Directory
{
    /**
     * Create a new directory.
     */
    public static function create(int $userId, string $name, ?int $parentId = null): ?int
    {
        return Database::insert('directories', [
            'user_id' => $userId,
            'name' => $name,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Rename a directory.
     */
    public static function rename(int $id, int $userId, string $newName): bool
    {
        return Database::update('directories', ['name' => $newName], ['id' => $id, 'user_id' => $userId]) > 0;
    }

    /**
     * Find a directory by ID and user ID.
     */
    public static function findById(int $id, int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM directories WHERE id = :id AND user_id = :user_id',
            ['id' => $id, 'user_id' => $userId]
        );
    }
    
    /**
     * Get the contents of a directory (subdirectories and files).
     */
    public static function getContents(int $userId, ?int $directoryId): array
    {
        $parentInfo = null;
        if ($directoryId) {
            $parentDir = self::findById($directoryId, $userId);
            if ($parentDir) {
                $parentInfo = $parentDir['parent_id'];
            }
        }

        return [
            'directories' => self::getDirectories($userId, $directoryId),
            'files' => File::getFiles($userId, $directoryId),
            'parent_id' => $parentInfo,
        ];
    }

    /**
     * Get subdirectories within a directory.
     */
    public static function getDirectories(int $userId, ?int $directoryId): array
    {
        $sql = "SELECT id, name, created_at, parent_id FROM directories WHERE user_id = :user_id AND " .
               ($directoryId ? "parent_id = :parent_id" : "parent_id IS NULL");
        
        $params = ['user_id' => $userId];
        if ($directoryId) {
            $params['parent_id'] = $directoryId;
        }

        return Database::fetchAll($sql, $params);
    }

    /**
     * Delete a directory after ensuring it's empty.
     */
    public static function delete(int $id, int $userId): bool
    {
        $subdirs = self::getDirectories($userId, $id);
        if (count($subdirs) > 0) {
            throw new Exception('Directory is not empty. Cannot delete a directory with subdirectories.');
        }

        $files = File::getFiles($userId, $id);
        if (count($files) > 0) {
            throw new Exception('Directory is not empty. Cannot delete a directory with files.');
        }

        return Database::delete('directories', ['id' => $id, 'user_id' => $userId]) > 0;
    }
}
