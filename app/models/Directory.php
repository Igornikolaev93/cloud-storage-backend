<?php
declare(strict_types=1);

namespace App\Models;

class Directory
{
    /**
     * Get all directories for a user within a specific parent directory.
     */
    public static function getDirectories(int $userId, ?int $parentId): array
    {
        $sql = "SELECT id, name, created_at FROM directories WHERE user_id = :user_id AND " .
               ($parentId ? "parent_id = :parent_id" : "parent_id IS NULL");

        $params = ['user_id' => $userId];
        if ($parentId) {
            $params['parent_id'] = $parentId;
        }

        return Database::fetchAll($sql, $params);
    }

    /**
     * Find a directory by its ID for a specific user.
     */
    public static function findById(int $directoryId, int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM directories WHERE id = :id AND user_id = :user_id',
            ['id' => $directoryId, 'user_id' => $userId]
        );
    }

    /**
     * Find the parent directory of a given directory.
     */
    public static function findParentDirectory(int $directoryId, int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT d_parent.* FROM directories d_child JOIN directories d_parent ON d_child.parent_id = d_parent.id WHERE d_child.id = :id AND d_child.user_id = :user_id',
            ['id' => $directoryId, 'user_id' => $userId]
        );
    }

    /**
     * Create a new directory.
     */
    public static function create(int $userId, string $name, ?int $parentId): bool
    {
        return Database::insert('directories', [
            'user_id' => $userId,
            'name' => $name,
            'parent_id' => $parentId,
            'created_at' => date('Y-m-d H:i:s')
        ]) !== null;
    }

    /**
     * Get the complete contents of a directory, including subdirectories and files.
     */
    public static function getContents(int $userId, ?int $parentId): array
    {
        $parentDir = null;
        if ($parentId) {
            $parentDir = self::findById($parentId, $userId);
        }

        $directories = self::getDirectories($userId, $parentId);
        $files = File::getFiles($userId, $parentId);

        return [
            'parent_id' => $parentDir ? $parentDir['parent_id'] : null,
            'directories' => $directories,
            'files' => $files
        ];
    }
    
    /**
     * Rename a directory.
     */
    public static function rename(int $directoryId, int $userId, string $newName): bool
    {
        return Database::update('directories', ['name' => $newName], ['id' => $directoryId, 'user_id' => $userId]) > 0;
    }

    /**
     * Delete a directory.
     */
    public static function delete(int $directoryId, int $userId): bool
    {
        return Database::delete('directories', ['id' => $directoryId, 'user_id' => $userId]) > 0;
    }
}
