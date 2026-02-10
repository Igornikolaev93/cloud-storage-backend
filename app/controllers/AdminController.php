<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Redirect non-admins to the login page
        if (!Auth::isAdmin()) {
            header('Location: /login');
            exit;
        }
    }

    public function index(): void
    {
        $users = User::getAll();
        $this->render('admin/index', ['users' => $users]);
    }

    public function editUser(): void
    {
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            header('Location: /admin/users');
            exit;
        }

        $user = User::findById((int)$userId);
        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        $this->render('admin/edit', ['user' => $user]);
    }

    public function updateUser(): void
    {
        $userId = $_POST['id'] ?? null;
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';

        if (!$userId) {
            header('Location: /admin/users');
            exit;
        }

        User::update((int)$userId, ['username' => $username, 'email' => $email, 'role' => $role]);

        header('Location: /admin/users');
        exit;
    }

    public function deleteUser(): void
    {
        $userId = $_POST['id'] ?? null;

        if ($userId) {
            User::delete((int)$userId);
        }

        header('Location: /admin/users');
        exit;
    }
}
