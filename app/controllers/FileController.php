<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Models\Share;
use App\Utils\Auth;
use App\Utils\Response;
use App\Utils\View;
use Exception;

class FileController extends BaseController
{
    private function renderFilesPage(array $data = []): void
    {
        $userId = Auth::id();
        $files = [];
        $sharedFiles = [];

        try {
            if ($userId) {
                $files = File::findByUser($userId);
                foreach ($files as &$file) {
                    $file['shared_with'] = Share::getUsersForFile((int)$file['id']);
                }
                $sharedFiles = Share::getFilesForUser($userId);
            }
        } catch (Exception $e) {
            $data['error'] = 'Could not fetch files: ' . $e->getMessage();
        }

        $user = Auth::user();
        View::render('files', array_merge([
            'files' => $files,
            'sharedFiles' => $sharedFiles,
            'fullName' => $user ? $user['first_name'] . ' ' . $user['last_name'] : ''
        ], $data));
    }

    /**
     * List all files for the authenticated user and show the files page.
     */
    public function list(): void
    {
        if (!Auth::id()) {
            header('Location: /login');
            exit;
        }
        $this->renderFilesPage();
    }

    /**
     * Add a new file from an upload.
     */
    public function add(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['file']['name'];
            
            try {
                $fileId = File::create($userId, $fileName, null);
                if ($fileId) {
                    header('Location: /');
                    exit;
                }
                $this->renderFilesPage(['error' => 'Failed to upload file.']);
            } catch (Exception $e) {
                $this->renderFilesPage(['error' => $e->getMessage()]);
            }
        } else {
            $this->renderFilesPage(['error' => 'No file uploaded or an error occurred.']);
        }
    }

    /**
     * Remove a file.
     */
    public function remove($id): void
    {
        $id = (int) $id;
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            if (File::remove($id, $userId)) {
                Response::json(['message' => 'File deleted successfully']);
                return;
            }
            Response::json(['error' => 'File not found or permission denied'], 404);
        } catch (Exception $e) {
            Response::json(['error' => 'Could not remove file: ' . $e->getMessage()], 500);
        }
    }
}
