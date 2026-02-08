<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use Exception;

class AdminController extends BaseController
{
    public function __construct()
    {
        if (!Auth::isAdmin()) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Forbidden'], 403);
            exit;
        }
    }

    public function getUsers(): void
    {
        try {
            $users = User::getAll();
            $this->sendJsonResponse(['status' => 'success', 'data' => $users]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(array $params): void
    {
        try {
            $userId = (int)$params['id'];
            if ($userId === Auth::getUserId()) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Cannot delete self'], 400);
                return;
            }

            if (User::delete($userId)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'User deleted']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
