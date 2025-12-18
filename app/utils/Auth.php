<?php
declare(strict_types=1);

namespace App\Utils;

class Auth
{
    /**
     * Start session if not already started.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Log in a user.
     */
    public static function login(array $user): void
    {
        self::start();
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ];
    }

    /**
     * Log out the current user.
     */
    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Check if a user is logged in.
     */
    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user']);
    }

    /**
     * Get the current authenticated user.
     */
    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get the current authenticated user's ID.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    /**
     * Check if the current user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        if (!self::check()) {
            return false;
        }
        $user = self::user();
        return isset($user['role']) && $user['role'] === $role;
    }
}
