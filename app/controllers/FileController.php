<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use Exception;

class FileController extends BaseController
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        if (Auth::check()) {
            $this->renderView('files');
        } else {
            header('Location: /login');
            exit;
        }
    }

    public function list(): void
    {
        try {
            $user = Auth::user();
            $userId = $user ? $user['id'] : null;

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
            $user = Auth::user();
            $userId = $user ? $user['id'] : null;

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
        $redirectUrl = '/files';
        try {
            $user = Auth::user();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                header('Location: /login');
                exit;
            }
            
            $directoryId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;
            if ($directoryId) {
                $redirectUrl .= '?dir=' . $directoryId;
            }

            if ($directoryId && !File::findDirectoryById($directoryId, $userId)) {
                throw new Exception('Directory not found');
            }

            if (empty($_FILES['file'])) {
                throw new Exception('No file uploaded');
            }

            $file = $_FILES['file'];
            $originalName = $file['name'];
            $mimeType = $file['type'];
            $size = $file['size'];
            $tmpName = $file['tmp_name'];
            
            $storedName = uniqid('file_', true) . '_' . $originalName;

            if (!defined('UPLOAD_DIR')) {
                define('UPLOAD_DIR', __DIR__ . '/../../uploads');
            }
            
            $uploadPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                File::createFile($userId, $directoryId, $originalName, $storedName, $mimeType, $size);
            } else {
                throw new Exception('Failed to move uploaded file');
            }

        } catch (Exception $e) {
            // Log the error message
            error_log($e->getMessage());
        } finally {
            // Redirect back to the files page
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function rename(): void
    {
        $redirectUrl = '/files';
        try {
            $user = Auth::user();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                header('Location: /login');
                exit;
            }

            $fileId = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $newName = isset($_POST['name']) ? trim($_POST['name']) : null;
            $directoryId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;

            if ($directoryId) {
                $redirectUrl .= '?dir=' . $directoryId;
            }

            if (!$fileId || !$newName) {
                throw new Exception('File ID and new name are required');
            }

            File::renameFile($fileId, $userId, $newName);

        } catch (Exception $e) {
            error_log($e->getMessage());
        } finally {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function remove(): void
    {
        $redirectUrl = '/files';
        try {
            $user = Auth::user();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                header('Location: /login');
                exit;
            }

            $fileId = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $directoryId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;

            if ($directoryId) {
                $redirectUrl .= '?dir=' . $directoryId;
            }
            
            if (!$fileId) {
                throw new Exception('File ID is required');
            }

            File::deleteFile($fileId, $userId);

        } catch (Exception $e) {
            error_log($e->getMessage());
        } finally {
            header('Location: ' . $redirectUrl);
            exit;
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
            $user = Auth::user();
            $ownerId = $user ? $user['id'] : null;

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
            $user = Auth::user();
            $ownerId = $user ? $user['id'] : null;

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
            $user = Auth::user();
            $ownerId = $user ? $user['id'] : null;

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
