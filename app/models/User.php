<?php
declare(strict_types=1);

namespace App\Models;

use Exception;
use PDO;

class User
{
    private int $id;
    private string $email;
    private string $passwordHash;
    private string $firstName;
    private string $lastName;
    private string $role;
    private string $createdAt;
    private string $updatedAt;
    
    /**
     * Создать пользователя
     */
    public static function create(array $data): ?int
    {
        $required = ['email', 'password', 'first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Проверяем уникальность email
        if (self::findByEmail($data['email'])) {
            throw new Exception("Email already exists");
        }
        
        $userData = [
            'email' => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'role' => $data['role'] ?? 'user'
        ];
        
        return Database::insert('users', $userData);
    }
    
    /**
     * Найти пользователя по ID
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT id, email, first_name, last_name, role, created_at, updated_at 
                FROM users WHERE id = ?";
        
        return Database::fetchOne($sql, [$id]);
    }
    
    /**
     * Найти пользователя по email
     */
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, email, password_hash, first_name, last_name, role 
                FROM users WHERE email = ?";
        
        return Database::fetchOne($sql, [strtolower(trim($email))]);
    }
    
    /**
     * Проверить учетные данные
     */
    public static function verifyCredentials(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Обновить профиль пользователя
     */
    public static function updateProfile(int $userId, array $data): bool
    {
        $allowedFields = ['first_name', 'last_name', 'email'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field]) && !empty(trim($data[$field]))) {
                $updateData[$field] = trim($data[$field]);
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        // Если обновляется email, проверяем уникальность
        if (isset($updateData['email'])) {
            $existing = self::findByEmail($updateData['email']);
            if ($existing && $existing['id'] != $userId) {
                throw new Exception("Email already exists");
            }
        }
        
        return Database::update('users', $updateData, ['id' => $userId]) > 0;
    }
    
    /**
     * Обновить пароль
     */
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
        
        return Database::update('users', 
            ['password_hash' => $passwordHash], 
            ['id' => $userId]
        ) > 0;
    }
    
    /**
     * Получить список пользователей (с пагинацией)
     */
    public static function getList(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT id, email, first_name, last_name, role, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $users = Database::fetchAll($sql, [$perPage, $offset]);
        
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
    
    /**
     * Поиск пользователей
     */
    public static function search(string $query, int $limit = 10): array
    {
        $sql = "SELECT id, email, first_name, last_name 
                FROM users 
                WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?
                LIMIT ?";
        
        $searchTerm = "%$query%";
        return Database::fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    /**
     * Удалить пользователя
     */
    public static function delete(int $userId): bool
    {
        // Начинаем транзакцию, так как нужно удалить связанные данные
        Database::beginTransaction();
        
        try {
            // Удаляем файлы пользователя
            $files = Database::fetchAll("SELECT id FROM files WHERE user_id = ?", [$userId]);
            foreach ($files as $file) {
                Database::delete('file_shares', ['file_id' => $file['id']]);
            }
            
            Database::delete('files', ['user_id' => $userId]);
            Database::delete('folders', ['user_id' => $userId]);
            Database::delete('sessions', ['user_id' => $userId]);
            
            // Удаляем самого пользователя
            $deleted = Database::delete('users', ['id' => $userId]) > 0;
            
            Database::commit();
            return $deleted;
            
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Изменить роль пользователя
     */
    public static function changeRole(int $userId, string $role): bool
    {
        if (!in_array($role, ['user', 'admin'])) {
            throw new Exception("Invalid role");
        }
        
        return Database::update('users', ['role' => $role], ['id' => $userId]) > 0;
    }
    
    /**
     * Создать токен для сброса пароля
     */
    public static function createPasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 час
        
        $data = [
            'user_id' => $userId,
            'token' => hash('sha256', $token),
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Удаляем старые токены
        Database::delete('password_reset_tokens', ['user_id' => $userId]);
        
        // Сохраняем новый токен
        Database::insert('password_reset_tokens', $data);
        
        return $token;
    }
    
    /**
     * Проверить токен сброса пароля
     */
    public static function verifyPasswordResetToken(string $token): ?int
    {
        $hashedToken = hash('sha256', $token);
        
        $sql = "SELECT user_id FROM password_reset_tokens 
                WHERE token = ? AND expires_at > NOW()";
        
        $result = Database::fetchOne($sql, [$hashedToken]);
        
        return $result ? (int)$result['user_id'] : null;
    }
    
    /**
     * Удалить токен сброса пароля
     */
    public static function deletePasswordResetToken(string $token): void
    {
        $hashedToken = hash('sha256', $token);
        Database::delete('password_reset_tokens', ['token' => $hashedToken]);
    }
}