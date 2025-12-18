<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\Response;
use Exception;

class UserController extends BaseController
{
    /**
     * User login.
     */
    public function login(): void
    {
        $data = $this->getRequestData();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            Response::json(['error' => 'Email and password are required'], 400);
            return;
        }

        try {
            $user = User::verifyCredentials($email, $password);
            if ($user) {
                Auth::login($user);
                Response::json(['message' => 'Login successful']);
            } else {
                Response::json(['error' => 'Invalid credentials'], 401);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Login failed', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * User logout.
     */
    public function logout(): void
    {
        Auth::logout();
        Response::json(['message' => 'Logout successful']);
    }

    /**
     * User registration.
     */
    public function register(): void
    {
        $data = $this->getRequestData();

        try {
            $userId = User::create($data);
            if ($userId) {
                Response::json(['message' => 'User created successfully', 'user_id' => $userId], 201);
            } else {
                Response::json(['error' => 'Failed to create user'], 500);
            }
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Send a password reset link to the user.
     */
    public function reset_password(): void
    {
        $data = $this->getRequestData();
        $email = $data['email'] ?? '';

        if (empty($email)) {
            Response::json(['error' => 'Email is required'], 400);
            return;
        }

        try {
            $user = User::findByEmail($email);
            if ($user) {
                // In a real application, you would send an email with a password reset link.
                // For now, we'll just return a success message.
                Response::json(['message' => 'A password reset link has been sent to your email address.']);
            } else {
                Response::json(['error' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not process request', 'message' => $e->getMessage()], 500);
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
     * Update the authenticated user's profile.
     */
    public function updateProfile(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = $this->getRequestData();

        try {
            $updated = User::updateProfile($userId, $data);
            if ($updated) {
                Response::json(['message' => 'Profile updated successfully']);
            } else {
                Response::json(['message' => 'Nothing to update']);
            }
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Search for a user by email.
     */
    public function search(string $email): void
    {
        try {
            $user = User::findByEmail($email);
            if ($user) {
                Response::json($user);
            } else {
                Response::json(['error' => 'User not found'], 404);
            }
        } catch (Exception $e) {
            Response::json(['error' => 'Could not fetch user', 'message' => $e->getMessage()], 500);
        }
    }
}
