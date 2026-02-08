<?php
declare(strict_types=1);

namespace App\Utils;

use App\Models\User;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function getUser(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return User::findById($_SESSION['user_id']);
    }

    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
        session_regenerate_id();
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_destroy();
    }

    public static function isAdmin(): bool
    {
        $user = self::getUser();
        return $user && $user['role'] === 'admin';
    }
}
