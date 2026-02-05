<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use Exception;

// --- FIX: The controller's constructor has been removed. ---
// The session is now started reliably in `index.php` and does not need to be managed here.
class DirectoryController extends BaseController
{
    public function add(): void
    {
        try {
            $user = Auth::user();
            if (!$user) {
                header('Location: /login');
                exit;
            }

            $name = isset($_POST['name']) ? trim($_POST['name']) : null;
            $parentId = !empty($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;

            if (!$name) {
                throw new Exception('Directory name is required.');
            }
            
            if ($parentId && !File::findDirectoryById($parentId, $user['id'])) {
                throw new Exception('Parent directory not found.');
            }

            $success = File::createDirectory($user['id'], $name, $parentId);

            if (!$success) {
                throw new Exception('Failed to create directory in the database.');
            }

            $redirectUrl = '/files';
            if ($parentId) {
                $redirectUrl .= '?dir=' . $parentId;
            }
            header('Location: ' . $redirectUrl);
            exit;

        } catch (Exception $e) {
            // Simplified error handling for a cleaner user experience
            error_log($e->getMessage());
            header('Location: /files?error=' . urlencode('Failed to create directory.'));
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

            $dirId = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $newName = isset($_POST['name']) ? trim($_POST['name']) : null;
            $parentId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;

            if ($parentId) {
                $redirectUrl .= '?dir=' . $parentId;
            }

            if (!$dirId || !$newName) {
                throw new Exception('Directory ID and new name are required');
            }

            File::renameDirectory($dirId, $userId, $newName);

        } catch (Exception $e) {
            error_log($e->getMessage());
        } finally {
            header('Location: ' . $redirectUrl);
            exit;
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

            $dirId = (int)$params['id'];
            
            $directory = File::findDirectoryById($dirId, $userId);
            if (!$directory) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Directory not found'], 404);
                return;
            }
            
            $contents = File::getDirectoryContents($userId, $dirId);
            $this->sendJsonResponse(['status' => 'success', 'data' => $contents]);

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
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

            $dirId = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $parentId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;

            if ($parentId) {
                $redirectUrl .= '?dir=' . $parentId;
            }

            if (!$dirId) {
                throw new Exception('Directory ID is required');
            }

            File::deleteDirectory($dirId, $userId);

        } catch (Exception $e) {
            error_log($e->getMessage());
        } finally {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}
