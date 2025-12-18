<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Models\Folder;
use Exception;

class FileController extends BaseController
{
    /**
     * Получить список файлов пользователя
     */
    public function list(): void
    {
        try {
            $folderId = $_GET['folder_id'] ?? null;
            
            if ($folderId) {
                // Проверяем существование папки и доступ
                $folder = Folder::getById((int)$folderId);
                if (!$folder || $folder['user_id'] != $this->user['id']) {
                    $this->notFound('Folder not found');
                    return;
                }
            }
            
            $files = File::getUserFiles($this->user['id'], $folderId ? (int)$folderId : null);
            
            $this->success(['files' => $files]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Загрузить файл
     */
    public function upload(): void
    {
        $files = $this->getUploadedFiles();
        
        if (empty($files['file'])) {
            $this->error('No file uploaded', 400);
            return;
        }
        
        try {
            $folderId = $_POST['folder_id'] ?? null;
            
            if ($folderId) {
                // Проверяем существование папки
                $folder = Folder::getById((int)$folderId);
                if (!$folder || $folder['user_id'] != $this->user['id']) {
                    $this->notFound('Folder not found');
                    return;
                }
            }
            
            $fileData = File::upload($this->user['id'], $files['file'], $folderId ? (int)$folderId : null);
            
            $this->success(['file' => $fileData], 'File uploaded successfully', 201);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Получить информацию о файле
     */
    public function get(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            $file = File::getById($fileId);
            
            if (!$file) {
                $this->notFound('File not found');
                return;
            }
            
            // Проверяем доступ
            if (!File::hasAccess($fileId, $this->user['id'])) {
                $this->forbidden();
                return;
            }
            
            $this->success(['file' => $file]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Обновить файл (переименовать/переместить)
     */
    public function update(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            $input = $this->getInput();
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            // Проверяем, что файл существует и пользователь - владелец
            $file = File::getById($fileId);
            if (!$file) {
                $this->notFound('File not found');
                return;
            }
            
            if ($file['user_id'] != $this->user['id']) {
                $this->forbidden();
                return;
            }
            
            // Если указана папка, проверяем её существование
            if (isset($input['folder_id'])) {
                if ($input['folder_id']) {
                    $folder = Folder::getById((int)$input['folder_id']);
                    if (!$folder || $folder['user_id'] != $this->user['id']) {
                        $this->error('Folder not found', 404);
                        return;
                    }
                } else {
                    $input['folder_id'] = null; // Перемещаем в корень
                }
            }
            
            $updated = File::update($fileId, $this->user['id'], $input);
            
            if ($updated) {
                $this->success(['updated' => true], 'File updated successfully');
            } else {
                $this->error('Failed to update file');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Удалить файл
     */
    public function delete(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            // Проверяем, что файл существует и пользователь - владелец
            $file = File::getById($fileId);
            if (!$file) {
                $this->notFound('File not found');
                return;
            }
            
            if ($file['user_id'] != $this->user['id']) {
                $this->forbidden();
                return;
            }
            
            $deleted = File::delete($fileId, $this->user['id']);
            
            if ($deleted) {
                $this->success(['deleted' => true], 'File deleted successfully');
            } else {
                $this->error('Failed to delete file');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Скачать файл
     */
    public function download(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            // Проверяем доступ
            if (!File::hasAccess($fileId, $this->user['id'])) {
                $this->forbidden();
                return;
            }
            
            $filePath = File::getFilePath($fileId);
            $file = File::getById($fileId);
            
            if (!$filePath || !$file) {
                $this->notFound('File not found');
                return;
            }
            
            // Отправляем файл
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . basename($file['name']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            exit;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Получить список общих файлов
     */
    public function listShared(): void
    {
        try {
            $files = File::getSharedFiles($this->user['id']);
            $this->success(['files' => $files]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Получить список пользователей, имеющих доступ к файлу
     */
    public function getShares(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            // Проверяем, что файл существует и пользователь - владелец
            $file = File::getById($fileId);
            if (!$file) {
                $this->notFound('File not found');
                return;
            }
            
            if ($file['user_id'] != $this->user['id']) {
                $this->forbidden();
                return;
            }
            
            $sharedUsers = File::getSharedUsers($fileId);
            $this->success(['shared_users' => $sharedUsers]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Поделиться файлом
     */
    public function addShare(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            $input = $this->getInput();
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            $errors = $this->validate($input, [
                'user_id' => 'required',
                'permission' => 'required'
            ]);
            
            if (!empty($errors)) {
                $this->error('Validation failed', 422, $errors);
                return;
            }
            
            if (!in_array($input['permission'], ['read', 'write'])) {
                $this->error('Invalid permission type', 422);
                return;
            }
            
            $shared = File::share(
                $fileId,
                $this->user['id'],
                (int)$input['user_id'],
                $input['permission']
            );
            
            if ($shared) {
                $this->success(['shared' => true], 'File shared successfully');
            } else {
                $this->error('Failed to share file');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
    /**
     * Удалить общий доступ
     */
    public function removeShare(): void
    {
        try {
            $fileId = (int)($_REQUEST['route_params']['id'] ?? 0);
            $input = $this->getInput();
            
            if (!$fileId) {
                $this->error('File ID is required', 400);
                return;
            }
            
            $errors = $this->validate($input, [
                'user_id' => 'required'
            ]);
            
            if (!empty($errors)) {
                $this->error('Validation failed', 422, $errors);
                return;
            }
            
            $unshared = File::unshare(
                $fileId,
                $this->user['id'],
                (int)$input['user_id']
            );
            
            if ($unshared) {
                $this->success(['unshared' => true], 'Access removed successfully');
            } else {
                $this->error('Failed to remove access');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}