<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Directory;
use App\Utils\Auth;
use Exception;

class DirectoryController extends BaseController
{
    public function add(): void
    {
        try {
            $user = Auth::getUser();
            if (!$user) {
                header('Location: /login');
                exit;
            }

            $name = isset($_POST['name']) ? trim($_POST['name']) : null;
            $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            if (!$name) {
                throw new Exception('Directory name is required.');
            }
            
            if ($parentId && !Directory::findById($parentId, $user['id'])) {
                throw new Exception('Parent directory not found.');
            }

            $success = Directory::create($user['id'], $name, $parentId);

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
            error_log($e->getMessage());
            header('Location: /files?error=' . urlencode('Failed to create directory.'));
            exit;
        }
    }

    public function rename(): void
    {
        $redirectUrl = '/files';
        try {
            $user = Auth::getUser();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                header('Location: /login');
                exit;
            }

            $dirId = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $newName = isset($_POST['name']) ? trim($_POST['name']) : null;
            $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            if ($parentId) {
                $redirectUrl .= '?dir=' . $parentId;
            }

            if (!$dirId || !$newName) {
                throw new Exception('Directory ID and new name are required');
            }

            Directory::rename($dirId, $userId, $newName);

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
            $user = Auth::getUser();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $dirId = isset($params['id']) ? (int)$params['id'] : null;
            
            $contents = Directory::getContents($userId, $dirId);
            $this->sendJsonResponse(['status' => 'success', 'data' => $contents]);

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function remove(): void
    {
        $redirectUrl = '/files';
        try {
            $user = Auth::getUser();
            $userId = $user ? $user['id'] : null;

            if (!$userId) {
                header('Location: /login');
                exit;
            }

            $dirId = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            if ($parentId) {
                $redirectUrl .= '?dir=' . $parentId;
            }

            if (!$dirId) {
                throw new Exception('Directory ID is required');
            }

            Directory::delete($dirId, $userId);

        } catch (Exception $e) {
            error_log($e->getMessage());
        } finally {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}
