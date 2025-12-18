<?php
declare(strict_types=1);

namespace App\Models;

use Exception;

class File
{
    /**
     * Create a new file record.
     */
    public static function create(int $userId, string $fileName, ?int $directoryId = null): ?int
    {
        $data = [
            'user_id' => $userId,
            'name' => $fileName,
            'directory_id' => $directoryId,
        ];

        return Database::insert('files', $data);
    }

    /**
     * Find files by user ID.
     */
    public static function findByUser(int $userId): array
    {
        $sql = "SELECT f.* FROM files f LEFT JOIN file_shares fs ON f.id = fs.file_id WHERE f.user_id = ? OR fs.user_id = ?";
        return Database::fetchAll($sql, [$userId, $userId]);
    }

    /**
     * Find a specific file by its ID and user ID.
     */
    public static function findById(int $id, int $userId): ?array
    {
        $sql = "SELECT f.* FROM files f LEFT JOIN file_shares fs ON f.id = fs.file_id WHERE f.id = ? AND (f.user_id = ? OR fs.user_id = ?)";
        return Database::fetchOne($sql, [$id, $userId, $userId]);
    }

    /**
     * Rename a file.
     */
    public static function rename(int $id, int $userId, string $newName): bool
    {
        $data = ['name' => $newName];
        $where = ['id' => $id, 'user_id' => $userId];

        return Database::update('files', $data, $where) > 0;
    }

    /**
     * Remove a file.
     */
    public static function remove(int $id, int $userId): bool
    {
        $where = ['id' => $id, 'user_id' => $userId];
        return Database::delete('files', $where) > 0;
    }

    /**
     * Get a list of users a file is shared with.
     */
    public static function getSharedUsers(int $id, int $userId): ?array
    {
        $file = self::findById($id, $userId);
        if (!$file || $file['user_id'] != $userId) {
            return null;
        }

        $sql = "SELECT u.id, u.email, u.first_name, u.last_name FROM users u JOIN file_shares fs ON u.id = fs.user_id WHERE fs.file_id = ?";
        return Database::fetchAll($sql, [$id]);
    }

    /**
     * Share a file with a user.
     */
    public static function share(int $id, int $userId, int $shareWithUserId): bool
    {
        $file = self::findById($id, $userId);
        if (!$file || $file['user_id'] != $userId) {
            return false;
        }

        $data = [
            'file_id' => $id,
            'user_id' => $shareWithUserId,
        ];

        try {
            return Database::insert('file_shares', $data) > 0;
        } catch (Exception $e) {
            // Ignore unique constraint violation
            return false;
        }
    }

    /**
     * Unshare a file with a user.
     */
    public static function unshare(int $id, int $userId, int $shareWithUserId): bool
    {
        $file = self::findById($id, $userId);
        if (!$file || $file['user_id'] != $userId) {
            return false;
        }

        $where = [
            'file_id' => $id,
            'user_id' => $shareWithUserId,
        ];

        return Database::delete('file_shares', $where) > 0;
    }
}
