<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use Exception;

class UserController extends BaseController
{
    /**
     * GET /user/search/{email}
     * Поиск пользователя по email
     */
    public function search(array $params): void
    {
        try {
            $userId = Auth::getUserId();
            if (!$userId) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 401);
                return;
            }

            $email = $params['email'] ?? null;
            if (!$email) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Email is required'], 400);
                return;
            }

            $user = User::findByEmail($email);

            if ($user) {
                // Возвращаем только публичную информацию
                $publicUserData = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ];
                $this->sendJsonResponse(['status' => 'success', 'data' => $publicUserData]);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
            }

        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
