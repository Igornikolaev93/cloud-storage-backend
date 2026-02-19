<?php
declare(strict_types=1);

namespace App\Models;

use Exception;

class File
{
    /**
     * Find files and folders by parent ID, for a specific user.
     */
    public static function findByParentId(int $userId, ?int $parentId): array
    {
        $sql = 'SELECT * FROM "files" WHERE "user_id" = :user_id AND ';

        if ($parentId === null) {
            $sql .= '"parent_id" IS NULL';
            $params = ['user_id' => $userId];
        } else {
            $sql .= '"parent_id" = :parent_id';
            $params = ['user_id' => $userId, 'parent_id' => $parentId];
        }

        $sql .= ' ORDER BY "is_folder" DESC, "filename" ASC';

        return Database::fetchAll($sql, $params);
    }

    /**
     * Get breadcrumbs for a given folder ID for a specific user.
     */
    public static function getBreadcrumbs(int $userId, ?int $parentId): array
    {
        $breadcrumbs = [];
        $currentId = $parentId;

        while ($currentId !== null) {
            $folder = self::findById($currentId);
            if (!$folder || $folder['user_id'] != $userId) {
                // Invalid ID or not owned by user, stop.
                break;
            }
            // Add to the beginning of the array
            array_unshift($breadcrumbs, $folder);
            $currentId = $folder['parent_id'];
        }

        return $breadcrumbs;
    }

    /**
     * Find a file or folder by its ID. It does not check for user ownership.
     * The controller is responsible for verifying the user owns the resource.
     */
    public static function findById(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM "files" WHERE "id" = :id', ['id' => $id]);
    }

    /**
     * Create a new folder record.
     */
    public static function createFolder(int $userId, ?int $parentId, string $filename): ?int
    {
        return Database::insert('files', [
            'user_id' => $userId,
            'parent_id' => $parentId,
            'filename' => $filename,
            'is_folder' => true,
        ]);
    }

    /**
     * Create a new file record.
     * The actual file content is expected to be stored elsewhere; this just creates the DB record.
     */
    public static function create(int $userId, ?int $parentId, string $filename, int $size, string $path, string $mimeType): ?int
    {
        return Database::insert('files', [
            'user_id' => $userId,
            'parent_id' => $parentId,
            'filename' => $filename,
            'is_folder' => false,
            'file_size' => $size,
            'file_path' => $path,
            'file_type' => $mimeType,
        ]);
    }
    
    /**
     * Rename a file or folder.
     */
    public static function rename(int $id, int $userId, string $newName): bool
    {
        return Database::update('files', ['filename' => $newName], ['id' => $id, 'user_id' => $userId]) > 0;
    }

    /**
     * Delete a file or folder (recursively for folders).
     */
    public static function delete(int $id, int $userId): bool
    {
        $item = self::findById($id);

        if (!$item || $item['user_id'] != $userId) {
            return false; // Not found or no permission
        }

        // If it's a folder, delete its children first
        if ($item['is_folder']) {
            $children = self::findByParentId($userId, $id);
            foreach ($children as $child) {
                self::delete($child['id'], $userId); // Recursive call
            }
        } else {
            // Optionally, delete the physical file
            if ($item['file_path'] && file_exists($item['file_path'])) {
                @unlink($item['file_path']);
            }
        }

        // Delete the item from the database
        return Database::delete('files', ['id' => $id]) > 0;
    }
}
