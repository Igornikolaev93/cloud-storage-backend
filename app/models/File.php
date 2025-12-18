<?php
declare(strict_types=1);

namespace App\Models;

use Exception;
use finfo;

class File
{
    /**
     * Загрузить файл
     */
    public static function upload(int $userId, array $fileData, ?int $folderId = null): array
    {
        // Проверяем ошибки загрузки
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $fileData['error']);
        }
        
        // Проверяем размер файла
        if ($fileData['size'] > MAX_FILE_SIZE) {
            throw new Exception("File too large. Maximum size: " . formatFileSize(MAX_FILE_SIZE));
        }
        
        // Проверяем тип файла
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);
        
        if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
            throw new Exception("File type not allowed");
        }
        
        // Генерируем уникальное имя для хранения
        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $storageName = uniqid('file_', true) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $storagePath = UPLOAD_DIR . '/' . $storageName;
        
        // Перемещаем файл
        if (!move_uploaded_file($fileData['tmp_name'], $storagePath)) {
            throw new Exception("Failed to move uploaded file");
        }
        
        // Создаем запись в БД
        $data = [
            'user_id' => $userId,
            'name' => basename($fileData['name']),
            'storage_name' => $storageName,
            'size' => $fileData['size'],
            'mime_type' => $mimeType,
            'folder_id' => $folderId
        ];
        
        $fileId = Database::insert('files', $data);
        
        return self::getById($fileId);
    }
    
    /**
     * Получить файл по ID
     */
    public static function getById(int $fileId): ?array
    {
        $sql = "SELECT f.*, u.email as owner_email, 
                       u.first_name as owner_first_name, u.last_name as owner_last_name
                FROM files f
                JOIN users u ON f.user_id = u.id
                WHERE f.id = ?";
        
        return Database::fetchOne($sql, [$fileId]);
    }
    
    /**
     * Получить файлы пользователя
     */
    public static function getUserFiles(int $userId, ?int $folderId = null): array
    {
        $sql = "SELECT * FROM files WHERE user_id = ?";
        $params = [$userId];
        
        if ($folderId !== null) {
            $sql .= " AND folder_id = ?";
            $params[] = $folderId;
        } else {
            $sql .= " AND folder_id IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Получить общие файлы
     */
    public static function getSharedFiles(int $userId): array
    {
        $sql = "SELECT f.*, u.email as owner_email, 
                       u.first_name as owner_first_name, u.last_name as owner_last_name,
                       fs.permission, fs.created_at as shared_at
                FROM files f
                JOIN file_shares fs ON f.id = fs.file_id
                JOIN users u ON f.user_id = u.id
                WHERE fs.user_id = ?
                ORDER BY fs.created_at DESC";
        
        return Database::fetchAll($sql, [$userId]);
    }
    
    /**
     * Обновить информацию о файле
     */
    public static function update(int $fileId, int $userId, array $data): bool
    {
        $allowedFields = ['name', 'folder_id'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return Database::update('files', $updateData, ['id' => $fileId, 'user_id' => $userId]) > 0;
    }
    
    /**
     * Удалить файл
     */
    public static function delete(int $fileId, int $userId): bool
    {
        // Получаем информацию о файле
        $file = self::getById($fileId);
        
        if (!$file || $file['user_id'] != $userId) {
            return false;
        }
        
        Database::beginTransaction();
        
        try {
            // Удаляем общий доступ
            Database::delete('file_shares', ['file_id' => $fileId]);
            
            // Удаляем запись из БД
            $deleted = Database::delete('files', ['id' => $fileId, 'user_id' => $userId]) > 0;
            
            if ($deleted) {
                // Удаляем физический файл
                $filePath = UPLOAD_DIR . '/' . $file['storage_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            Database::commit();
            return $deleted;
            
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Поделиться файлом
     */
    public static function share(int $fileId, int $ownerId, int $userId, string $permission = 'read'): bool
    {
        // Проверяем существование файла и владельца
        $file = self::getById($fileId);
        if (!$file || $file['user_id'] != $ownerId) {
            throw new Exception("File not found or access denied");
        }
        
        // Нельзя поделиться с самим собой
        if ($ownerId == $userId) {
            throw new Exception("Cannot share with yourself");
        }
        
        // Проверяем существование пользователя
        $user = User::findById($userId);
        if (!$user) {
            throw new Exception("User not found");
        }
        
        $data = [
            'file_id' => $fileId,
            'user_id' => $userId,
            'shared_by' => $ownerId,
            'permission' => $permission
        ];
        
        try {
            Database::insert('file_shares', $data);
            return true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                throw new Exception("File already shared with this user");
            }
            throw $e;
        }
    }
    
    /**
     * Удалить общий доступ
     */
    public static function unshare(int $fileId, int $ownerId, int $userId): bool
    {
        return Database::delete('file_shares', [
            'file_id' => $fileId,
            'user_id' => $userId,
            'shared_by' => $ownerId
        ]) > 0;
    }
    
    /**
     * Получить список пользователей, имеющих доступ к файлу
     */
    public static function getSharedUsers(int $fileId): array
    {
        $sql = "SELECT fs.*, u.email, u.first_name, u.last_name
                FROM file_shares fs
                JOIN users u ON fs.user_id = u.id
                WHERE fs.file_id = ?
                ORDER BY fs.created_at DESC";
        
        return Database::fetchAll($sql, [$fileId]);
    }
    
    /**
     * Проверить доступ к файлу
     */
    public static function hasAccess(int $fileId, int $userId): bool
    {
        // Владелец всегда имеет доступ
        $file = self::getById($fileId);
        if ($file && $file['user_id'] == $userId) {
            return true;
        }
        
        // Проверяем общий доступ
        $sql = "SELECT COUNT(*) FROM file_shares WHERE file_id = ? AND user_id = ?";
        return Database::fetchColumn($sql, [$fileId, $userId]) > 0;
    }
    
    /**
     * Получить путь к файлу
     */
    public static function getFilePath(int $fileId): ?string
    {
        $file = self::getById($fileId);
        if (!$file) {
            return null;
        }
        
        $path = UPLOAD_DIR . '/' . $file['storage_name'];
        return file_exists($path) ? $path : null;
    }
    
    /**
     * Получить статистику по файлам
     */
    public static function getStats(int $userId): array
    {
        $sql = "SELECT 
                COUNT(*) as total_files,
                SUM(size) as total_size,
                COUNT(DISTINCT mime_type) as unique_types
                FROM files WHERE user_id = ?";
        
        $stats = Database::fetchOne($sql, [$userId]) ?? [];
        
        // Добавляем человекочитаемые форматы
        if (isset($stats['total_size'])) {
            $stats['total_size_formatted'] = formatFileSize((int)$stats['total_size']);
        }
        
        return $stats;
    }
}