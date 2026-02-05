<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use Exception;

class File
{
    // ... (существующие методы createDirectory, createFile) ...
    public static function createDirectory(int $userId, string $name, ?int $parentId = null): ?int
    {
        // Проверка на существование директории с таким же именем в той же родительской папке
        $sql = "SELECT id FROM directories WHERE user_id = :user_id AND name = :name AND parent_id " . ($parentId ? "= :parent_id" : "IS NULL");
        $params = ['user_id' => $userId, 'name' => $name];
        if ($parentId) {
            $params['parent_id'] = $parentId;
        }
        if (Database::fetchOne($sql, $params)) {
            throw new Exception("Directory with this name already exists in the target location.");
        }

        $data = [
            'user_id' => $userId,
            'name' => $name,
            'parent_id' => $parentId
        ];
        return Database::insert('directories', $data);
    }

    public static function createFile(int $userId, ?int $directoryId, string $originalName, string $storedName, string $mimeType, int $size): ?int
    {
        $data = [
            'user_id' => $userId,
            'directory_id' => $directoryId,
            'name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => $size
        ];
        return Database::insert('files', $data);
    }

    /**
     * Получение списка файлов и папок в указанной директории
     * + список файлов, которыми поделились с пользователем (в корне)
     */
    public static function getDirectoryContents(int $userId, ?int $directoryId = null): array
    {
        // Получаем информацию о текущей директории, включая ее родителя
        $currentDirectory = null;
        $parentId = null;
        if ($directoryId) {
            $currentDirectory = self::findDirectoryById($directoryId, $userId);
            if ($currentDirectory) {
                $parentId = $currentDirectory['parent_id'];
            }
        } 

        $dirSql = "SELECT id, name, parent_id, created_at, updated_at FROM directories WHERE user_id = :user_id AND parent_id " . ($directoryId ? "= :directory_id" : "IS NULL");
        $directories = Database::fetchAll($dirSql, ['user_id' => $userId] + ($directoryId ? ['directory_id' => $directoryId] : []));

        $fileSql = "SELECT id, name, mime_type, size, created_at, updated_at FROM files WHERE user_id = :user_id AND " . ($directoryId ? "directory_id = :directory_id" : "directory_id IS NULL");
        $files = Database::fetchAll($fileSql, ['user_id' => $userId] + ($directoryId ? ['directory_id' => $directoryId] : []));


        // Если мы в корневой папке, добавляем файлы, которыми поделились с пользователем
        if ($directoryId === null) {
            $sharedFiles = self::getSharedFiles($userId);
            $files = array_merge($files, $sharedFiles);
        }

        return [
            'directories' => $directories,
            'files' => $files,
            'parent_id' => $parentId, 
        ];
    }

    /**
     * Получение информации о файле по ID, с проверкой прав доступа (владелец или расшарен)
     */
    public static function findFileById(int $fileId, int $userId): ?array
    {
        // Сначала проверяем, является ли пользователь владельцем
        $sql = "SELECT * FROM files WHERE id = :file_id AND user_id = :user_id";
        $file = Database::fetchOne($sql, ['file_id' => $fileId, 'user_id' => $userId]);

        if ($file) {
            return $file;
        }

        // Если не владелец, проверяем, поделились ли с ним файлом
        $sql = "SELECT f.* FROM files f JOIN file_shares fs ON f.id = fs.file_id WHERE f.id = :file_id AND fs.user_id = :user_id";
        return Database::fetchOne($sql, ['file_id' => $fileId, 'user_id' => $userId]);
    }

    // ... (существующий метод findDirectoryById) ...
    public static function findDirectoryById(int $id, int $userId): ?array
    {
        $sql = "SELECT * FROM directories WHERE id = :id AND user_id = :user_id";
        return Database::fetchOne($sql, ['id' => $id, 'user_id' => $userId]);
    }

    // --- Новые методы для управления доступом ---

    /**
     * Поделиться файлом с другим пользователем
     */
    public static function shareFile(int $fileId, int $ownerId, int $userIdToShareWith): bool
    {
        // Проверяем, что текущий пользователь является владельцем файла
        $file = Database::fetchOne("SELECT id, user_id FROM files WHERE id = :id", ['id' => $fileId]);
        if (!$file || $file['user_id'] !== $ownerId) {
            throw new Exception("File not found or you are not the owner.");
        }
        
        // Нельзя поделиться файлом с самим собой
        if ($ownerId === $userIdToShareWith) {
            throw new Exception("You cannot share a file with yourself.");
        }

        // Проверяем, что пользователь, с которым делятся, существует
        $userToShare = User::findById($userIdToShareWith);
        if (!$userToShare) {
            throw new Exception("User to share with not found.");
        }

        // Проверяем, не поделились ли уже файлом с этим пользователем
        $existingShare = Database::fetchOne("SELECT id FROM file_shares WHERE file_id = :file_id AND user_id = :user_id", [
            'file_id' => $fileId,
            'user_id' => $userIdToShareWith
        ]);
        if ($existingShare) {
            throw new Exception("File is already shared with this user.");
        }

        $data = [
            'file_id' => $fileId,
            'user_id' => $userIdToShareWith
        ];
        return Database::insert('file_shares', $data) !== null;
    }

    /**
     * Прекратить доступ к файлу для пользователя
     */
    public static function unshareFile(int $fileId, int $ownerId, int $userIdToUnshare): bool
    {
        // Проверяем, что текущий пользователь является владельцем файла
        $file = Database::fetchOne("SELECT id, user_id FROM files WHERE id = :id", ['id' => $fileId]);
        if (!$file || $file['user_id'] !== $ownerId) {
            throw new Exception("File not found or you are not the owner.");
        }

        return Database::delete('file_shares', ['file_id' => $fileId, 'user_id' => $userIdToUnshare]) > 0;
    }

    /**
     * Получить список пользователей, с которыми поделен файл
     */
    public static function getSharedWith(int $fileId, int $ownerId): array
    {
        // Проверяем, что текущий пользователь является владельцем файла
        $file = Database::fetchOne("SELECT id, user_id FROM files WHERE id = :id", ['id' => $fileId]);
        if (!$file || $file['user_id'] !== $ownerId) {
            throw new Exception("File not found or you are not the owner.");
        }

        $sql = "SELECT u.id, u.email, u.first_name, u.last_name FROM users u JOIN file_shares fs ON u.id = fs.user_id WHERE fs.file_id = :file_id";
        return Database::fetchAll($sql, ['file_id' => $fileId]);
    }

    /**
     * Получить список файлов, которыми поделились с текущим пользователем
     */
    public static function getSharedFiles(int $userId): array
    {
        $sql = "SELECT f.id, f.name, f.mime_type, f.size, f.created_at, f.updated_at, o.email as owner_email FROM files f JOIN file_shares fs ON f.id = fs.file_id JOIN users o ON f.user_id = o.id WHERE fs.user_id = :user_id";
        return Database::fetchAll($sql, ['user_id' => $userId]);
    }

    // ... (остальные методы renameFile, renameDirectory, deleteFile, deleteDirectory) ...
    public static function renameFile(int $fileId, int $userId, string $newName): bool
    {
        // Проверка прав и существования файла
        $file = self::findFileById($fileId, $userId);
        if (!$file) {
            throw new Exception("File not found or access denied.");
        }
        
        return Database::update('files', ['name' => $newName], ['id' => $fileId, 'user_id' => $userId]) > 0;
    }

    public static function renameDirectory(int $dirId, int $userId, string $newName): bool
    {
        // Проверка прав и существования директории
        $dir = self::findDirectoryById($dirId, $userId);
        if (!$dir) {
            throw new Exception("Directory not found or access denied.");
        }

        return Database::update('directories', ['name' => $newName], ['id' => $dirId, 'user_id' => $userId]) > 0;
    }

    public static function deleteFile(int $fileId, int $userId): bool
    {
        $file = self::findFileById($fileId, $userId);
        if (!$file) {
            throw new Exception("File not found or access denied.");
        }

        // Удаление файла из файловой системы
        $filePath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $file['stored_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Удаление записи из БД
        return Database::delete('files', ['id' => $fileId]) > 0;
    }

    public static function deleteDirectory(int $dirId, int $userId): bool
    {
        $dir = self::findDirectoryById($dirId, $userId);
        if (!$dir) {
            throw new Exception("Directory not found or access denied.");
        }

        // Рекурсивно удаляем все вложенные директории
        $subDirs = Database::fetchAll("SELECT id FROM directories WHERE parent_id = :dir_id", ['dir_id' => $dirId]);
        foreach ($subDirs as $subDir) {
            self::deleteDirectory((int)$subDir['id'], $userId);
        }

        // Удаляем все файлы в этой директории
        $files = Database::fetchAll("SELECT id FROM files WHERE directory_id = :dir_id", ['dir_id' => $dirId]);
        foreach ($files as $file) {
            self::deleteFile((int)$file['id'], $userId);
        }

        // Удаляем саму директорию
        return Database::delete('directories', ['id' => $dirId]) > 0;
    }
}
