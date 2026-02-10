<?php
declare(strict_types=1);

namespace App\Models;

use Exception;
use PDO;

class User
{
    public static function create(array $data): ?int
    {
        if (self::findByUsername($data['username'])) {
            throw new Exception('Username already exists');
        }

        if (self::findByEmail($data['email'])) {
            throw new Exception('Email already exists');
        }

        $userData = [
            'email' => strtolower(trim($data['email'])),
            'username' => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ];

        if (isset($data['role'])) {
            $userData['role'] = $data['role'];
        }

        return Database::insert('users', $userData);
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT id, email, username, created_at, updated_at, role 
                FROM users WHERE id = :id";

        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        return Database::fetchOne($sql, ['email' => strtolower(trim($email))]);
    }

    public static function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        return Database::fetchOne($sql, ['username' => $username]);
    }
    
    public static function getAll(): array
    {
        $sql = "SELECT id, email, username, created_at, updated_at, role FROM users ORDER BY created_at DESC";
        return Database::fetchAll($sql);
    }

    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        return Database::update('users',
            ['password_hash' => $passwordHash],
            ['id' => $userId]
        ) > 0;
    }
    
    public static function update(int $userId, array $data): bool
    {
        $allowedFields = ['email', 'username', 'role'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($updateData)) {
            return false;
        }

        if (isset($updateData['email'])) {
            $existingUser = self::findByEmail($updateData['email']);
            if ($existingUser && $existingUser['id'] !== $userId) {
                throw new Exception('Email address is already in use by another account.');
            }
        }
        
        return Database::update('users', $updateData, ['id' => $userId]) > 0;
    }

    public static function delete(int $userId): bool
    {
        return Database::delete('users', ['id' => $userId]) > 0;
    }

    public static function createPasswordResetToken(int $userId, string $token): void
    {
        $data = [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600) // 1 hour expiry
        ];
        Database::insert('password_resets', $data);
    }

    public static function findByPasswordResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()";
        $reset = Database::fetchOne($sql, ['token' => $token]);

        if ($reset) {
            return self::findById((int)$reset['user_id']);
        }
        return null;
    }

    public static function deletePasswordResetToken(string $token): void
    {
        Database::delete('password_resets', ['token' => $token]);
    }
}
