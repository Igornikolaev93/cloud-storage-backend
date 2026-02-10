<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Directory;
use App\Models\File;
use App\Utils\Auth;
use Exception;

class FileController extends BaseController
{
    public function index(): void
    {
        if (Auth::check()) {
            $this->render('files');
        } else {
            header('Location: /login');
            exit;
        }
    }

    public function list(): void
    {
        try {
            $user = Auth::getUser();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }
            
            $parentId = !empty($_GET['dir']) ? (int)$_GET['dir'] : null;

            $contents = Directory::getContents($userId, $parentId);
            $this->sendJsonResponse(['status' => 'success', 'data' => $contents]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function upload(): void
    {
        try {
            $user = Auth::getUser();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            if (empty($_FILES['files'])) {
                throw new Exception('No files were uploaded.');
            }

            $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            $files = $_FILES['files'];
            $count = count($files['name']);

            for ($i = 0; $i < $count; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    continue; // Or handle the error
                }

                File::create($userId, $parentId, $file['name'], $file['size'], $file['tmp_name']);
            }

            $this->sendJsonResponse(['status' => 'success', 'message' => 'Files uploaded successfully.']);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
