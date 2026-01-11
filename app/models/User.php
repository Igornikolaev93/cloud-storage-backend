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

    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        return Database::update('users',
            ['password' => $passwordHash],
            ['id' => $userId]
        ) > 0;
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
    
    public static function getList(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT id, email, first_name, last_name, role, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $users = Database::fetchAll($sql, ['limit' => $perPage, 'offset' => $offset]);
        
        // Получаем общее количество
        $total = Database::fetchColumn("SELECT COUNT(*) FROM users");
        
        return [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
    
    public static function search(string $query, int $limit = 10): array
    {
        $sql = "SELECT id, email, first_name, last_name 
                FROM users 
                WHERE email LIKE :query OR first_name LIKE :query OR last_name LIKE :query
                LIMIT :limit";
        
        $searchTerm = "%$query%";
        return Database::fetchAll($sql, ['query' => $searchTerm, 'limit' => $limit]);
    }
    
    public static function delete(int $userId): bool
    {
        return Database::delete('users', ['id' => $userId]) > 0;
    }
    
    public static function changeRole(int $userId, string $role): bool
    {
        if (!in_array($role, ['user', 'admin'])) {
            throw new Exception("Invalid role");
        }
        
        return Database::update('users', ['role' => $role], ['id' => $userId]) > 0;
    }
}
