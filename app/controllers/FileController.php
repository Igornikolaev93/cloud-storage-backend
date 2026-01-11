<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use Exception;

class FileController extends BaseController
{
    // ... (существующие методы list, get, add, rename, remove) ...
    public function list(): void
    {
        try {
            $userId = Auth::getUserId(); // Предполагаем, что Auth::getUserId() возвращает ID текущего пользователя
            if (!$userId) {
                $this->sendJsonResponse([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
                return;
            }
            
            $contents = File::getDirectoryContents($userId, null); // null для корневой директории
            $this->sendJsonResponse(['status' => 'success', 'data' => $contents]);

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function get(array $params): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $fileId = (int)$params['id'];
            $file = File::findFileById($fileId, $userId);

            if (!$file) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'File not found'], 404);
                return;
            }

            $this->sendJsonResponse(['status' => 'success', 'data' => $file]);

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function add(): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }
            
            $directoryId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;
            if ($directoryId) {
                // Убедимся, что директория принадлежит пользователю
                if (!File::findDirectoryById($directoryId, $userId)) {
                     $this->sendJsonResponse(['status' => 'error', 'message' => 'Directory not found'], 404);
                     return;
                }
            } else {
                 $this->sendJsonResponse(['status' => 'error', 'message' => 'Directory ID is required'], 400);
                 return;
            }

            if (empty($_FILES['file'])) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'No file uploaded'], 400);
                return;
            }

            $file = $_FILES['file'];
            $originalName = $file['name'];
            $mimeType = $file['type'];
            $size = $file['size'];
            $tmpName = $file['tmp_name'];
            
            // Генерируем уникальное имя файла
            $storedName = uniqid('file_', true) . '_' . $originalName;
            $uploadPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                $fileId = File::createFile($userId, $directoryId, $originalName, $storedName, $mimeType, $size);
                if ($fileId) {
                    $this->sendJsonResponse(['status' => 'success', 'message' => 'File uploaded successfully', 'file_id' => $fileId]);
                } else {
                    // Если не удалось создать запись в БД, удаляем загруженный файл
                    unlink($uploadPath);
                    $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to save file info to database'], 500);
                }
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to move uploaded file'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function rename(): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            // Получаем данные из тела PUT-запроса
            parse_str(file_get_contents("php://input"), $data);
            $fileId = isset($data['id']) ? (int)$data['id'] : null;
            $newName = isset($data['name']) ? trim($data['name']) : null;

            if (!$fileId || !$newName) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'File ID and new name are required'], 400);
                return;
            }

            if (File::renameFile($fileId, $userId, $newName)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'File renamed successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to rename file'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function remove(array $params): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $fileId = (int)$params['id'];

            if (File::deleteFile($fileId, $userId)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'File deleted successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to delete file'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    // --- Новые методы для управления доступом ---

    /**
     * GET /files/share/{id}
     * Получить список пользователей, имеющих доступ к файлу
     */
    public function getSharedUsers(array $params): void
    {
        try {
            $ownerId = Auth::getUserId();
            if (!$ownerId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $fileId = (int)$params['id'];
            $users = File::getSharedWith($fileId, $ownerId);
            $this->sendJsonResponse(['status' => 'success', 'data' => $users]);

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /files/share/{id}/{user_id}
     * Добавить доступ к файлу пользователю
     */
    public function shareWithUser(array $params): void
    {
        try {
            $ownerId = Auth::getUserId();
            if (!$ownerId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $fileId = (int)$params['id'];
            $userIdToShareWith = (int)$params['user_id'];

            if (File::shareFile($fileId, $ownerId, $userIdToShareWith)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'File shared successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to share file'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /files/share/{id}/{user_id}
     * Прекратить доступ к файлу для пользователя
     */
    public function unshareWithUser(array $params): void
    {
        try {
            $ownerId = Auth::getUserId();
            if (!$ownerId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $fileId = (int)$params['id'];
            $userIdToUnshare = (int)$params['user_id'];

            if (File::unshareFile($fileId, $ownerId, $userIdToUnshare)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Access to file has been revoked']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to revoke access'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
