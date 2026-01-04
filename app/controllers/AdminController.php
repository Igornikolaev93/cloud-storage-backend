<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\View;
use Exception;

class AdminController extends BaseController
{
    public function __construct()
    {
        // The route filter in index.php already protects all admin routes.
        // A duplicate check here is redundant.
    }

    /**
     * Show the user management page.
     */
    public function users(): void
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $result = User::getList($page, 10); // 10 users per page
            
            View::render('admin/users', [
                'users' => $result['users'],
                'pagination' => $result['pagination'],
                'user_role' => Auth::user()['role']
            ]);
        } catch (Exception $e) {
            View::render('admin/users', ['error' => 'Could not fetch users: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle user role change.
     */
    public function changeRole(int $userId): void
    {
        $newRole = $_POST['role'] ?? '';

        if (empty($newRole)) {
            header('Location: /admin/users');
            exit;
        }

        try {
            // Prevent an admin from accidentally removing their own admin status
            if ($userId === Auth::id() && $newRole !== 'admin') {
                throw new Exception('You cannot remove your own admin status.');
            }
            
            User::changeRole($userId, $newRole);
            header('Location: /admin/users');
            exit;
        } catch (Exception $e) {
            $result = User::getList();
            View::render('admin/users', [
                'users' => $result['users'],
                'pagination' => $result['pagination'],
                'error' => $e->getMessage(),
                'user_role' => Auth::user()['role']
            ]);
        }
    }

    /**
     * Handle user deletion.
     */
    public function deleteUser(int $userId): void
    {
        try {
            // Prevent an admin from deleting their own account
            if ($userId === Auth::id()) {
                throw new Exception('You cannot delete your own account.');
            }

            User::delete($userId);
            header('Location: /admin/users?message=User deleted successfully');
            exit;
        } catch (Exception $e) {
            header('Location: /admin/users?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
