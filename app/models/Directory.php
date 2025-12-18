<?php
declare(strict_types=1);

namespace App\Models;

use Exception;

class Directory
{
    /**
     * Create a new directory.
     */
    public static function create(int $userId, string $name): ?int
    {
        $data = [
            'user_id' => $userId,
            'name' => $name,
        ];

        return Database::insert('directories', $data);
    }

    /**
     * Rename a directory.
     */
    public static function rename(int $id, int $userId, string $newName): bool
    {
        $data = ['name' => $newName];
        $where = ['id' => $id, 'user_id' => $userId];

        return Database::update('directories', $data, $where) > 0;
    }

    /**
     * Find a directory by ID and user ID, including its files.
     */
    public static function findById(int $id, int $userId): ?array
    {
        $sql = "SELECT * FROM directories WHERE id = ? AND user_id = ?";
        $directory = Database::fetchOne($sql, [$id, $userId]);

        if ($directory) {
            $sql = "SELECT * FROM files WHERE directory_id = ? AND user_id = ?";
            $directory['files'] = Database::fetchAll($sql, [$id, $userId]);
        }

        return $directory;
    }

    /**
     * Delete a directory and all its files.
     */
    public static function delete(int $id, int $userId): bool
    {
        Database::beginTransaction();

        try {
            // Delete all files in the directory
            Database::delete('files', ['directory_id' => $id, 'user_id' => $userId]);

            // Delete the directory itself
            $deleted = Database::delete('directories', ['id' => $id, 'user_id' => $userId]) > 0;

            Database::commit();
            return $deleted;

        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
}
