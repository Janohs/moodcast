<?php

namespace App\Models;

use App\Services\DatabaseService;
use PDO;
use PDOException;

class User
{
    private $db;
    
    public function __construct()
    {
        $this->db = DatabaseService::getInstance()->getConnection();
    }
    
    /**
     * Create a new user
     */
    public function create($userData)
    {
        try {
            $sql = "INSERT INTO users (id, email, name, password_hash, preferred_weather, created_at, updated_at) 
                    VALUES (:id, :email, :name, :password_hash, :preferred_weather, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($sql);
            
            $userId = $this->generateUUID();
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            $stmt->execute([
                'id' => $userId,
                'email' => $userData['email'],
                'name' => $userData['name'],
                'password_hash' => $hashedPassword,
                'preferred_weather' => json_encode($userData['weather_preferences'] ?? [])
            ]);
            
            return [
                'id' => $userId,
                'email' => $userData['email'],
                'name' => $userData['name']
            ];
            
        } catch (PDOException $e) {
            throw new \Exception('Failed to create user: ' . $e->getMessage());
        }
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['weather_preferences'] = json_decode($user['preferred_weather'], true) ?? [];
            }
            
            return $user;
            
        } catch (PDOException $e) {
            throw new \Exception('Failed to find user: ' . $e->getMessage());
        }
    }
    
    /**
     * Find user by ID
     */
    public function findById($id)
    {
        try {
            $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['weather_preferences'] = json_decode($user['preferred_weather'], true) ?? [];
                // Don't return password hash in find operations
                unset($user['password_hash']);
            }
            
            return $user;
            
        } catch (PDOException $e) {
            throw new \Exception('Failed to find user: ' . $e->getMessage());
        }
    }
    
    /**
     * Update user preferences
     */
    public function updatePreferences($userId, $preferences)
    {
        try {
            $sql = "UPDATE users SET preferred_weather = :preferences, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'preferences' => json_encode($preferences),
                'id' => $userId
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            throw new \Exception('Failed to update preferences: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }
    
    /**
     * Generate UUID v4
     */
    private function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
