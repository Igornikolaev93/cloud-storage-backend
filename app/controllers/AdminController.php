<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\Response;
use Exception;

class AdminController extends BaseController
{
    public function __construct()
    {
        // Ensure the user is an admin for all methods in this controller
        if (!Auth::hasRole('admin')) {
            Response::json(['error' => 'Forbidden'], 403);
            exit;
        }
    }

    /**
     * Get a list of users.
     */
    public function listUsers(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;

        try {
            $users = User::getList($page, $perPage);
            Response::json($users);
        } catch (Exception $e) {
            Response::json(['error' => 'Could not fetch users', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific user by ID.
     */
    public function getUser(int $id): void
    {
        try {
            $user = User::findById($id);
            if ($user) {
                Response::json($user);
            } else {
                Response::json(['error' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not fetch user', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a user's information.
     */
    public function updateUser(int $id): void
    {
        $data = $this->getRequestData();

        try {
            // Update profile data
            if (isset($data['first_name']) || isset($data['last_name']) || isset($data['email'])) {
                User::updateProfile($id, $data);
            }

            // Update role
            if (isset($data['role'])) {
                User::changeRole($id, $data['role']);
            }

            Response::json(['message' => 'User updated successfully']);

        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): void
    {
        try {
            $deleted = User::delete($id);
            if ($deleted) {
                Response::json(['message' => 'User deleted successfully']);
            } else {
                Response::json(['error' => 'User not found or could not be deleted'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not delete user', 'message' => $e->getMessage()], 500);
        }
    }
}
