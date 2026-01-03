<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use App\Utils\View;
use Exception;

class FileController extends BaseController
{
    /**
     * List all files for the authenticated user and show the files page.
     */
    public function list(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        try {
            $user = Auth::user();
            $files = File::findByUser($userId);
            View::render('files', [
                'files' => $files,
                'fullName' => $user['first_name'] . ' ' . $user['last_name']
            ]);
        } catch (Exception $e) {
            View::render('files', ['error' => 'Could not fetch files: ' . $e->getMessage()]);
        }
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
                // In a real application, you would move the uploaded file to a secure location.
                // For this example, we'll just add a record to the database.
                $fileId = File::create($userId, $fileName, null);
                if ($fileId) {
                    header('Location: /');
                    exit;
                } else {
                    View::render('files', ['error' => 'Failed to upload file']);
                }
            } catch (Exception $e) {
                View::render('files', ['error' => $e->getMessage()]);
            }
        } else {
            View::render('files', ['error' => 'No file uploaded or an error occurred']);
        }
    }

    /**
     * Remove a file.
     */
    public function remove(int $id): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        try {
            $deleted = File::remove($id, $userId);
            if ($deleted) {
                header('Location: /');
                exit;
            } else {
                View::render('files', ['error' => 'File not found or permission denied']);
            }
        } catch (Exception $e) {
            View::render('files', ['error' => 'Could not remove file: ' . $e->getMessage()]);
        }
    }
}
