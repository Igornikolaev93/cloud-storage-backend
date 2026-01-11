<?php
declare(strict_types=1);

namespace App\Models;

use Exception;
use PDO;

class User
{
    public static function create(array $data): ?int
    {
        if (self::findByEmail($data['email'])) {
            throw new Exception("Email already exists");
        }

        $userData = [
            'email' => strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'role' => $data['role'] ?? 'user'
        ];

        return Database::insert('users', $userData);
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT id, email, first_name, last_name, role, created_at, updated_at 
                FROM users WHERE id = :id";

        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        return Database::fetchOne($sql, ['email' => strtolower(trim($email))]);
    }
    
    /**
     * Получение всех пользователей (для админки)
     */
    public static function getAll(): array
    {
        $sql = "SELECT id, email, first_name, last_name, role, created_at, updated_at FROM users ORDER BY created_at DESC";
        return Database::fetchAll($sql);
    }

    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        return Database::update('users',
            ['password' => $passwordHash],
            ['id' => $userId]
        ) > 0;
    }
    
    /**
     * Обновление данных пользователя (для админки)
     */
    public static function update(int $userId, array $data): bool
    {
        $allowedFields = ['email', 'first_name', 'last_name', 'role'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        // Пароль обновляется отдельно, если он был передан
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($updateData)) {
            return false; // Нет данных для обновления
        }

        // Проверка уникальности email, если он меняется
        if (isset($updateData['email'])) {
            $existingUser = self::findByEmail($updateData['email']);
            if ($existingUser && $existingUser['id'] !== $userId) {
                throw new Exception("Email address is already in use by another account.");
            }
        }
        
        // Проверка корректности роли
        if (isset($updateData['role']) && !in_array($updateData['role'], ['user', 'admin'])) {
            throw new Exception("Invalid role specified.");
        }

        return Database::update('users', $updateData, ['id' => $userId]) > 0;
    }

    /**
     * Удаление пользователя (для админки)
     */
    public static function delete(int $userId): bool
    {
        // В будущем здесь можно добавить логику для удаления связанных данных (файлов и т.д.),
        // но пока что ON DELETE CASCADE в БД справляется с этим.
        return Database::delete('users', ['id' => $userId]) > 0;
    }

    public static function createPasswordResetToken(string $email, string $token): void
    {
        $user = self::findByEmail($email);
        if (!$user) {
            return;
        }

        $data = [
            'email' => $email,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s')
        ];
        Database::insert('password_resets', $data);
    }

    public static function findUserByPasswordResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM password_resets WHERE token = :token AND created_at >= NOW() - INTERVAL '1 hour'";
        $reset = Database::fetchOne($sql, ['token' => $token]);

        if ($reset) {
            return self::findByEmail($reset['email']);
        }
        return null;
    }

    public static function deletePasswordResetToken(string $token): void
    {
        Database::delete('password_resets', ['token' => $token]);
    }
}
