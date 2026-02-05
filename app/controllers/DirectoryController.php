<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use Exception;

class DirectoryController extends BaseController
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * POST /directories/add
     * Добавление папки (директории)
     */
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

            $name = isset($_POST['name']) ? trim($_POST['name']) : null;
            $parentId = isset($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;

            if ($parentId) {
                $redirectUrl .= '?dir=' . $parentId;
            }

            if (!$name) {
                throw new Exception('Directory name is required');
            }
            
            if ($parentId && !File::findDirectoryById($parentId, $userId)) {
                throw new Exception('Parent directory not found');
            }

            File::createDirectory($userId, $name, $parentId);

        } catch (Exception $e) {
            // Log the error message
            error_log($e->getMessage());
        } finally {
            // Redirect back to the files page
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * POST /directories/rename
     * Переименование папки
     */
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

    /**
     * GET /directories/get/{id}
     * Получение информации о папке (список файлов и подпапок)
     */
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
            
            // Проверяем, существует ли директория и принадлежит ли она пользователю
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

    /**
     * POST /directories/remove
     * Удаление папки
     */
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
