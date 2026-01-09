<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Directory;
use App\Utils\Auth;
use App\Utils\Response;
use Exception;

class DirectoryController extends BaseController
{
    /**
     * Add a new directory.
     */
    public function add(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = $this->getRequestData();
        $name = $data['name'] ?? '';

        if (empty($name)) {
            Response::json(['error' => 'Directory name is required'], 400);
            return;
        }

        try {
            $directoryId = Directory::create($userId, $name);
            if ($directoryId) {
                Response::json(['message' => 'Directory created successfully', 'directory_id' => $directoryId], 201);
            } else {
                Response::json(['error' => 'Failed to create directory'], 500);
            }
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Rename a directory.
     */
    public function rename(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = $this->getRequestData();
        $directoryId = $data['id'] ?? 0;
        $newName = $data['name'] ?? '';

        if (!$directoryId || !$newName) {
            Response::json(['error' => 'Directory ID and new name are required'], 400);
            return;
        }

        try {
            $renamed = Directory::rename((int)$directoryId, $userId, $newName);
            if ($renamed) {
                Response::json(['message' => 'Directory renamed successfully']);
            } else {
                Response::json(['error' => 'Directory not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get a specific directory by ID, including its files.
     */
    public function get($id): void
    {
        $id = (int) $id;
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $directory = Directory::findById($id, $userId);
            if ($directory) {
                Response::json($directory);
            } else {
                Response::json(['error' => 'Directory not found'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not fetch directory', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a directory.
     */
    public function delete($id): void
    {
        $id = (int) $id;
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $deleted = Directory::delete($id, $userId);
            if ($deleted) {
                Response::json(['message' => 'Directory deleted successfully']);
            } else {
                Response::json(['error' => 'Directory not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not delete directory', 'message' => $e->getMessage()], 500);
        }
    }
}
