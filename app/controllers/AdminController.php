<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use Exception;

class AdminController extends BaseController
{
    /**
     * Конструктор для проверки прав администратора перед выполнением любого действия
     */
    public function __construct()
    {
        try {
            $user = Auth::getUser(); // Получаем всего пользователя, а не только ID
            if (!$user || $user['role'] !== 'admin') {
                // Если пользователь не авторизован или не является админом, прерываем выполнение
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Forbidden: Administrator access required.'], 403);
                exit; // Прекращаем выполнение скрипта
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
            exit;
        }
    }

    /**
     * GET /admin/users/list
     * Получение списка всех пользователей
     */
    public function listUsers(): void
    {
        try {
            $users = User::getAll();
            $this->sendJsonResponse(['status' => 'success', 'data' => $users]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /admin/users/get/{id}
     * Получение информации по конкретному пользователю
     */
    public function getUser(array $params): void
    {
        try {
            $userId = (int)$params['id'];
            $user = User::findById($userId);
            if (!$user) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
                return;
            }
            $this->sendJsonResponse(['status' => 'success', 'data' => $user]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /admin/users/delete/{id}
     * Удаление пользователя
     */
    public function deleteUser(array $params): void
    {
        try {
            $userId = (int)$params['id'];
             // Предотвращаем удаление самого себя
            if ($userId === Auth::getUserId()) {
                $this->sendJsonResponse(['status' => 'error', 'message' => \'Administrators cannot delete their own account.\'], 400);
                return;
            }
            
            if (User::delete($userId)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'User deleted successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'User not found or could not be deleted'], 404);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /admin/users/update/{id}
     * Обновление информации пользователя
     */
    public function updateUser(array $params): void
    {
        try {
            $userId = (int)$params['id'];

            // Получаем данные из тела PUT-запроса
            parse_str(file_get_contents("php://input"), $data);

            // Здесь должна быть валидация данных ($data)

            if (User::update($userId, $data)) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'User updated successfully']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'User not found or could not be updated'], 404);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
