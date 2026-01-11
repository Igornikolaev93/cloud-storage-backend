<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use Exception;

class DirectoryController extends BaseController
{
    /**
     * POST /directories/add
     * Добавление папки (директории)
     */
    public function add(): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $name = isset($_POST['name']) ? trim($_POST['name']) : null;
            $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            if (!$name) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Directory name is required'], 400);
                return;
            }
            
            // Если parent_id указан, проверяем, что он принадлежит текущему пользователю
            if ($parentId && !File::findDirectoryById($parentId, $userId)) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Parent directory not found'], 404);
                return;
            }

            $dirId = File::createDirectory($userId, $name, $parentId);
            if ($dirId) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Directory created successfully', 'directory_id' => $dirId]);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to create directory'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /directories/rename
     * Переименование папки
     */
    public function rename(): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            parse_str(file_get_contents("php://input"), $data);
            $dirId = isset($data['id']) ? (int)$data['id'] : null;
            $newName = isset($data['name']) ? trim($data['name']) : null;

            if (!$dirId || !$newName) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Directory ID and new name are required'], 400);
                return;
            }

            if (File::renameDirectory($dirId, $userId, $newName)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Directory renamed successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to rename directory'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /directories/get/{id}
     * Получение информации о папке (список файлов и подпапок)
     */
    public function get(array $params): void
    {
        try {
            $userId = Auth::getUserId();
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
     * DELETE /directories/delete/{id}
     * Удаление папки
     */
    public function delete(array $params): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $dirId = (int)$params['id'];

            if (File::deleteDirectory($dirId, $userId)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Directory deleted successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to delete directory'], 500);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
