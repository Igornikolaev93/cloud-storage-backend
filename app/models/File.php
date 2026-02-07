<?php
declare(strict_types=1);

namespace App\Models;

use Exception;

class File
{
    public static function getDirectoryContents(int $userId, ?int $directoryId): array
    {
        $parentInfo = null;
        if ($directoryId) {
            $parentDir = self::findDirectoryById($directoryId, $userId);
            if ($parentDir) {
                $parentInfo = $parentDir['parent_id'];
            }
        }

        return [
            'directories' => self::getDirectories($userId, $directoryId),
            'files' => self::getFiles($userId, $directoryId),
            'parent_id' => $parentInfo,
        ];
    }
    
    public static function getDirectories(int $userId, ?int $directoryId): array
    {
        $sql = "SELECT id, name, created_at as created_at, parent_id FROM directories WHERE user_id = :user_id AND " . 
               ($directoryId ? "parent_id = :parent_id" : "parent_id IS NULL");
        
        $params = ['user_id' => $userId];
        if ($directoryId) {
            $params['parent_id'] = $directoryId;
        }

        return Database::fetchAll($sql, $params);
    }

    public static function getFiles(int $userId, ?int $directoryId): array
    {
        $sql = "SELECT id, file_name as name, upload_date as created_at, file_size as size FROM files WHERE user_id = :user_id AND " . 
               ($directoryId ? "parent_id = :parent_id" : "parent_id IS NULL");

        $params = ['user_id' => $userId];
        if ($directoryId) {
            $params['parent_id'] = $directoryId;
        }

        return Database::fetchAll($sql, $params);
    }
    
    public static function findDirectoryById(int $directoryId, int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM directories WHERE id = :id AND user_id = :user_id',
            ['id' => $directoryId, 'user_id' => $userId]
        );
    }

    public static function findFileById(int $fileId, int $userId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM files WHERE id = :id AND user_id = :user_id',
            ['id' => $fileId, 'user_id' => $userId]
        );
    }
    
    public static function createDirectory(int $userId, string $name, ?int $parentId = null): bool
    {
        return Database::insert('directories', [
            'user_id' => $userId,
            'name' => $name,
            'parent_id' => $parentId
        ]) !== null;
    }

    public static function createFile(int $userId, ?int $directoryId, string $originalName, string $storedName, string $mimeType, int $size): bool
    {
        return Database::insert('files', [
            'user_id' => $userId,
            'parent_id' => $directoryId,
            'file_name' => $originalName,
            'file_path' => $storedName,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'upload_date' => date('Y-m-d H:i:s')
        ]) !== null;
    }
    
    public static function renameFile(int $fileId, int $userId, string $newName): bool
    {
        return Database::update('files', ['file_name' => $newName], ['id' => $fileId, 'user_id' => $userId]) > 0;
    }

    public static function deleteFile(int $fileId, int $userId): bool
    {
        return Database::delete('files', ['id' => $fileId, 'user_id' => $userId]) > 0;
    }

    public static function renameDirectory(int $dirId, int $userId, string $newName): bool
    {
        return Database::update('directories', ['name' => $newName], ['id' => $dirId, 'user_id' => $userId]) > 0;
    }
    
    public static function deleteDirectory(int $dirId, int $userId): bool
    {
        $subdirs = self::getDirectories($userId, $dirId);
        if (count($subdirs) > 0) {
            throw new Exception('Directory is not empty. Cannot delete a directory with subdirectories.');
        }

        $files = self::getFiles($userId, $dirId);
        if (count($files) > 0) {
            throw new Exception('Directory is not empty. Cannot delete a directory with files.');
        }

        return Database::delete('directories', ['id' => $dirId, 'user_id' => $userId]) > 0;
    }
}
