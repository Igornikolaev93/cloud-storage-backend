<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\File;
use App\Utils\Auth;
use App\Utils\Response;
use Exception;

class FileController extends BaseController
{
    /**
     * List all files for the authenticated user.
     */
    public function list(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $files = File::findByUser($userId);
            Response::json($files);
        } catch (Exception $e) {
            Response::json(['error' => 'Could not fetch files', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific file by ID.
     */
    public function get(int $id): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $file = File::findById($id, $userId);
            if ($file) {
                // In a real application, you would generate a secure, temporary download link.
                // For now, we just return the file information.
                Response::json($file);
            } else {
                Response::json(['error' => 'File not found or no access'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not fetch file', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Add a new file.
     */
    public function add(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        // In a real application, you would handle file uploads from $_FILES.
        // For this example, we'll simulate creating a file from request data.
        $data = $this->getRequestData();
        $fileName = $data['name'] ?? 'new_file.txt';
        $directoryId = $data['directory_id'] ?? null;

        try {
            $fileId = File::create($userId, $fileName, $directoryId);
            if ($fileId) {
                Response::json(['message' => 'File created successfully', 'file_id' => $fileId], 201);
            } else {
                Response::json(['error' => 'Failed to create file'], 500);
            }
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Rename a file.
     */
    public function rename(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = $this->getRequestData();
        $fileId = $data['id'] ?? 0;
        $newName = $data['name'] ?? '';

        if (!$fileId || !$newName) {
            Response::json(['error' => 'File ID and new name are required'], 400);
            return;
        }

        try {
            $renamed = File::rename($fileId, $userId, $newName);
            if ($renamed) {
                Response::json(['message' => 'File renamed successfully']);
            } else {
                Response::json(['error' => 'File not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove a file.
     */
    public function remove(int $id): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $deleted = File::remove($id, $userId);
            if ($deleted) {
                Response::json(['message' => 'File removed successfully']);
            } else {
                Response::json(['error' => 'File not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not remove file', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a list of users a file is shared with.
     */
    public function getSharedUsers(int $id): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $users = File::getSharedUsers($id, $userId);
            if ($users !== null) {
                Response::json($users);
            } else {
                Response::json(['error' => 'File not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not get shared users', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Share a file with a user.
     */
    public function share(int $id, int $shareWithUserId): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $shared = File::share($id, $userId, $shareWithUserId);
            if ($shared) {
                Response::json(['message' => 'File shared successfully']);
            } else {
                Response::json(['error' => 'File not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not share file', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Unshare a file with a user.
     */
    public function unshare(int $id, int $shareWithUserId): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $unshared = File::unshare($id, $userId, $shareWithUserId);
            if ($unshared) {
                Response::json(['message' => 'File unshared successfully']);
            } else {
                Response::json(['error' => 'File not found or permission denied'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not unshare file', 'message' => $e->getMessage()], 500);
        }
    }
}
