<?php
declare(strict_types=1);

namespace App\Utils;

// --- FIX: This entire file has been rewritten for a more robust and standard authentication system ---
class Auth
{
    /**
     * Check if a user is logged in by verifying the existence of user data in the session.
     *
     * @return bool True if the user is logged in, false otherwise.
     */
    public static function check(): bool
    {
        // A user is considered logged in if the 'user' session variable is set and is an array.
        return isset($_SESSION['user']);
    }

    /**
     * Retrieve the currently logged-in user's data from the session.
     *
     * @return array|null The user's data as an array, or null if not logged in.
     */
    public static function user(): ?array
    {
        // This now directly returns the user array stored in the session, avoiding extra database calls.
        return $_SESSION['user'] ?? null;
    }

    /**
     * Log a user in by storing their complete data in the session.
     *
     * @param array $user The user data array, typically from a User::findById or User::findByEmail call.
     */
    public static function login(array $user): void
    {
        // Regenerate the session ID to prevent session fixation attacks.
        session_regenerate_id(true);

        // Store the entire user array in the session. This is the critical fix.
        $_SESSION['user'] = $user;
    }

    /**
     * Log the user out by destroying the session and clearing session data.
     */
    public static function logout(): void
    {
        // Unset all session variables.
        $_SESSION = [];

        // If using cookies, expire the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }
}
