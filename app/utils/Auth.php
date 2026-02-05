<?php
declare(strict_types=1);

namespace App\Utils;

class Auth
{
    /**
     * Log in a user.
     */
    public static function login(array $user): void
    {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'username' => $user['username']
        ];
    }

    /**
     * Log out the current user.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Check if a user is logged in.
     */
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    /**
     * Get the current authenticated user.
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}