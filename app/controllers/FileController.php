<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use Exception;

class FileController extends BaseController
{
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
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }
            
            $contents = File::getDirectoryContents($userId, null);
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
        $directoryId = !empty($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;
        if ($directoryId) {
            $redirectUrl .= '?dir=' . $directoryId;
        }

        try {
            $user = Auth::user();
            if (!$user) {
                header('Location: /login');
                exit;
            }
            $userId = $user['id'];

            if ($directoryId && !File::findDirectoryById($directoryId, $userId)) {
                throw new Exception('The destination directory does not exist.');
            }

            if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed. Please choose a file and try again.');
            }

            $file = $_FILES['file'];
            $originalName = basename($file['name']);
            $tmpName = $file['tmp_name'];
            $storedName = uniqid('file_', true) . '_' . $originalName;

            if (!defined('UPLOAD_DIR')) {
                define('UPLOAD_DIR', __DIR__ . '/../../uploads');
            }

            if (!is_dir(UPLOAD_DIR) || !is_writable(UPLOAD_DIR)) {
                throw new Exception("The server's upload directory is not configured correctly. Please contact support.");
            }
            
            $uploadPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                $success = File::createFile($userId, $directoryId, $originalName, $storedName, $file['type'], $file['size']);
                if (!$success) {
                    unlink($uploadPath);
                    throw new Exception('Failed to save file metadata to the database.');
                }
            } else {
                throw new Exception('Failed to move the uploaded file. Check server permissions.');
            }

            header('Location: ' . $redirectUrl);
            exit;

        } catch (Exception $e) {
            $errorMessage = urlencode($e->getMessage());
            $separator = strpos($redirectUrl, '?') === false ? '?' : '&';
            header('Location: ' . $redirectUrl . $separator . 'error=' . $errorMessage);
            exit;
        }
    }

    public function download(array $params): void
    {
        try {
            $user = Auth::user();
            if (!$user) {
                header('Location: /login');
                exit;
            }
            $userId = $user['id'];

            if (!isset($params['id'])) {
                throw new Exception('File ID is missing.');
            }
            $fileId = (int)$params['id'];

            $file = File::findFileById($fileId, $userId);

            if (!$file) {
                throw new Exception('File not found or you do not have permission to access it.');
            }

            if (!defined('UPLOAD_DIR')) {
                define('UPLOAD_DIR', __DIR__ . '/../../uploads');
            }

            $filePath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $file['file_path'];

            if (!file_exists($filePath) || !is_readable($filePath)) {
                throw new Exception('File is not accessible on the server.');
            }

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . basename($file['name']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $file['file_size']);

            ob_clean();
            flush();

            readfile($filePath);
            exit;

        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
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
}
