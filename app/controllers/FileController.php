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
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $userId = Auth::getUser()['id'];
        $parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;

        $files = File::findByParentId($userId, $parentId);
        $breadcrumbs = File::getBreadcrumbs($userId, $parentId);
        $currentParentId = $parentId ?? 'null';

        $this->render('files', [
            'files' => $files,
            'breadcrumbs' => $breadcrumbs,
            'currentParentId' => $currentParentId
        ]);
    }

    public function createFolder(): void
    {
        if (!Auth::check()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $userId = Auth::getUser()['id'];
            $folderName = trim($_POST['folder_name']);
            $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== 'null' ? (int)$_POST['parent_id'] : null;

            if (empty($folderName)) {
                throw new Exception('Folder name cannot be empty.');
            }

            $folderId = File::createFolder($userId, $parentId, $folderName);
            $folder = File::findById($folderId);

            $this->sendJsonResponse(['status' => 'success', 'message' => 'Folder created successfully.', 'data' => $folder]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function upload(): void
    {
        if (!Auth::check()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        try {
            $userId = Auth::getUser()['id'];
            $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== 'null' ? (int)$_POST['parent_id'] : null;

            // Expect a single file upload with name 'file'
            if (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('No file was uploaded.');
            }

            if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('An error occurred during file upload.');
            }

            $tmpName = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            $fileSize = $_FILES['file']['size'];
            
            $fileId = File::create($userId, $parentId, $fileName, $fileSize, $tmpName);
            if (!$fileId) {
                throw new Exception('Failed to save the file to the database.');
            }

            $uploadedFile = File::findById($fileId);

            $this->sendJsonResponse(['status' => 'success', 'message' => 'File uploaded successfully.', 'data' => $uploadedFile]);
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

    public function delete(): void
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
}
