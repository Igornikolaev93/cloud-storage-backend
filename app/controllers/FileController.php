<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Directory;
use App\Models\File;
use App\Models\User;
use App\Utils\Auth;
use Exception;

class FileController extends BaseController
{
    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::getUser();
        $userId = $user['id'];

        try {
            // Pass the root directory structure and user info to the view
            $this->render('files', [
                'user' => $user,
            ]);
        } catch (Exception $e) {
            // Handle potential errors, like database connection issues
            $this->render('files', [
                'user' => $user,
                'error' => 'Could not retrieve file data: ' . $e->getMessage(),
            ]);
        }
    }

    public function list(): void
    {
        if (!Auth::check()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $userId = Auth::getUser()['id'];
            $parentId = isset($_GET['parent_id']) && $_GET['parent_id'] !== 'null' ? (int)$_GET['parent_id'] : null;
            
            $contents = Directory::getContents($userId, $parentId);
            $this->sendJsonResponse(['status' => 'success', 'data' => $contents]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function get(int $id): void
    {
        // Implementation for getting a single file's details
    }

    public function add(): void
    {
        if (!Auth::check()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $userId = Auth::getUser()['id'];
            $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== 'null' ? (int)$_POST['parent_id'] : null;

            if (empty($_FILES['files'])) {
                throw new Exception('No files were uploaded.');
            }

            $uploadedFiles = [];
            foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue; // Or handle the specific error
                }
                $fileName = $_FILES['files']['name'][$key];
                $fileSize = $_FILES['files']['size'][$key];
                
                $fileId = File::create($userId, $parentId, $fileName, $fileSize, $tmpName);
                if ($fileId) {
                    $uploadedFiles[] = File::findById($fileId);
                }
            }

            $this->sendJsonResponse(['status' => 'success', 'message' => 'Files uploaded successfully.', 'data' => $uploadedFiles]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
    
    public function rename(): void
    {
        if (!Auth::check()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $userId = Auth::getUser()['id'];
            $fileId = (int)$_POST['id'];
            $newName = trim($_POST['name']);

            if (empty($newName)) {
                throw new Exception('New name cannot be empty.');
            }

            if (!File::rename($fileId, $userId, $newName)) {
                throw new Exception('Failed to rename file. You may not have permission or the file does not exist.');
            }

            $this->sendJsonResponse(['status' => 'success', 'message' => 'File renamed successfully.']);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function remove(): void
    {
        if (!Auth::check()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $userId = Auth::getUser()['id'];
            $fileId = (int)$_POST['id'];

            if (!File::delete($fileId, $userId)) {
                throw new Exception('Failed to delete file. You may not have permission or the file does not exist.');
            }

            $this->sendJsonResponse(['status' => 'success', 'message' => 'File deleted successfully.']);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function download(int $id): void
    {
        // Implementation for downloading a file
    }

    // Methods for sharing
    public function getSharedUsers(int $id): void {}
    public function shareWithUser(int $id, int $userId): void {}
    public function unshareWithUser(int $id, int $userId): void {}
}