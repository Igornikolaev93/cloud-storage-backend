<?php
declare(strict_types=1);

namespace App\Models;

class Share
{
    /**
     * Share a file with a user.
     */
    public static function add(int $fileId, int $userId): ?int
    {
        // Check if the share already exists
        $existingShare = Database::fetchOne(
            'SELECT id FROM file_shares WHERE file_id = :file_id AND user_id = :user_id',
            ['file_id' => $fileId, 'user_id' => $userId]
        );

        if ($existingShare) {
            return (int)$existingShare['id'];
        }

        return Database::insert('file_shares', ['file_id' => $fileId, 'user_id' => $userId]);
    }

    /**
     * Unshare a file from a user.
     */
    public static function remove(int $fileId, int $userId): int
    {
        return Database::delete('file_shares', ['file_id' => $fileId, 'user_id' => $userId]);
    }

    /**
     * Get all users a file is shared with.
     */
    public static function getUsersForFile(int $fileId): array
    {
        return Database::fetchAll(
            'SELECT u.id, u.email FROM users u JOIN file_shares fs ON u.id = fs.user_id WHERE fs.file_id = :file_id',
            ['file_id' => $fileId]
        );
    }

    /**
     * Get all files shared with a user.
     */
    public static function getFilesForUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT f.* FROM files f JOIN file_shares fs ON f.id = fs.file_id WHERE fs.user_id = :user_id',
            ['user_id' => $userId]
        );
    }
}
