<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;

class AdminController extends BaseController
{
    public function users(): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/');
        }

        $user = new User();
        $users = $user->findAll();

        $this->render('admin/users', ['users' => $users]);
    }

    public function editUser(): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/');
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/users');
        }

        $user = new User();
        $userData = $user->find((int)$id);

        if (!$userData) {
            $this->redirect('/admin/users');
        }

        $this->render('admin/edit_user', ['user' => $userData]);
    }

    public function updateUser(): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/');
        }

        $id = $_POST['id'] ?? null;
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';

        if ($id) {
            $user = new User();
            $user->update((int)$id, [
                'username' => $username,
                'email' => $email,
                'role' => $role,
            ]);
        }

        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/');
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $user = new User();
            $user->delete((int)$id);
        }

        $this->redirect('/admin/users');
    }
}
