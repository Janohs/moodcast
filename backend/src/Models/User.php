<?php

namespace App\Models;

use App\Services\DatabaseService;
use App\Services\SupabaseService;
use PDO;
use PDOException;

class User
{
    private $db;
    private $supabase;
    
    public function __construct()
    {
        $this->db = DatabaseService::getInstance()->getConnection();
        $this->supabase = new SupabaseService();
    }
    
    /**
     * Create a new user
     */
    public function create($userData)
    {
        $userId = $this->generateUUID();
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $userRecord = [
            'id' => $userId,
            'email' => $userData['email'],
            'name' => $userData['name'],
            'password_hash' => $hashedPassword,
            'preferred_weather' => $userData['weather_preferences'] ?? []
        ];

        try {
            // Try to create in Supabase first
            $supabaseUser = $this->supabase->createUser($userRecord);
            
            if ($supabaseUser) {
                // If Supabase succeeds, also create in local SQLite for backup/caching
                $this->createLocalUser($userRecord);
                
                return [
                    'id' => $supabaseUser['id'],
                    'email' => $supabaseUser['email'],
                    'name' => $supabaseUser['name']
                ];
            } else {
                throw new \Exception('Failed to create user in Supabase');
            }

        } catch (\Exception $e) {
            // Fallback to local SQLite if Supabase fails
            error_log('Supabase user creation failed, falling back to local: ' . $e->getMessage());
            return $this->createLocalUser($userRecord);
        }
    }

    /**
     * Create user in local SQLite database
     */
    private function createLocalUser($userRecord)
    {
        try {
            $sql = "INSERT INTO users (id, email, name, password_hash, preferred_weather, created_at, updated_at) 
                    VALUES (:id, :email, :name, :password_hash, :preferred_weather, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                'id' => $userRecord['id'],
                'email' => $userRecord['email'],
                'name' => $userRecord['name'],
                'password_hash' => $userRecord['password_hash'],
                'preferred_weather' => json_encode($userRecord['preferred_weather'])
            ]);
            
            return [
                'id' => $userRecord['id'],
                'email' => $userRecord['email'],
                'name' => $userRecord['name']
            ];
            
        } catch (PDOException $e) {
            throw new \Exception('Failed to create user locally: ' . $e->getMessage());
        }
    }
     /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        try {
            // Try Supabase first
            $user = $this->supabase->findUserByEmail($email);
            
            if ($user) {
                // Sync to local database for caching
                $this->syncUserToLocal($user);
                return $user;
            }

        } catch (\Exception $e) {
            error_log('Supabase findByEmail failed, trying local: ' . $e->getMessage());
        }

        // Fallback to local SQLite
        return $this->findByEmailLocal($email);
    }

    /**
     * Find user by email in local database
     */
    private function findByEmailLocal($email)
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
            throw new \Exception('Failed to find user locally: ' . $e->getMessage());
        }
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        try {
            // Try Supabase first
            $user = $this->supabase->findUserById($id);
            
            if ($user) {
                return $user;
            }

        } catch (\Exception $e) {
            error_log('Supabase findById failed, trying local: ' . $e->getMessage());
        }

        // Fallback to local SQLite
        return $this->findByIdLocal($id);
    }

    /**
     * Find user by ID in local database
     */
    private function findByIdLocal($id)
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
            throw new \Exception('Failed to find user by ID locally: ' . $e->getMessage());
        }
    }
    
    /**
     * Update user preferences
     */
    public function updatePreferences($userId, $preferences)
    {
        try {
            // Try Supabase first
            $success = $this->supabase->updateUserPreferences($userId, $preferences);
            
            if ($success) {
                // Also update local database
                $this->updatePreferencesLocal($userId, $preferences);
                return true;
            }

        } catch (\Exception $e) {
            error_log('Supabase updatePreferences failed, trying local: ' . $e->getMessage());
        }

        // Fallback to local SQLite
        return $this->updatePreferencesLocal($userId, $preferences);
    }

    /**
     * Update user preferences in local database
     */
    private function updatePreferencesLocal($userId, $preferences)
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
            throw new \Exception('Failed to update preferences locally: ' . $e->getMessage());
        }
    }

    /**
     * Sync user data to local database for caching
     */
    private function syncUserToLocal($supabaseUser)
    {
        try {
            // Check if user exists locally
            $localUser = $this->findByEmailLocal($supabaseUser['email']);
            
            if ($localUser) {
                // Update existing local user
                $sql = "UPDATE users SET name = :name, preferred_weather = :preferred_weather, updated_at = CURRENT_TIMESTAMP WHERE email = :email";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'name' => $supabaseUser['name'],
                    'preferred_weather' => is_array($supabaseUser['preferred_weather']) 
                        ? json_encode($supabaseUser['preferred_weather']) 
                        : $supabaseUser['preferred_weather'],
                    'email' => $supabaseUser['email']
                ]);
            } else {
                // Create new local user (without password_hash for security)
                $sql = "INSERT INTO users (id, email, name, password_hash, preferred_weather, created_at, updated_at) 
                        VALUES (:id, :email, :name, '', :preferred_weather, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'id' => $supabaseUser['id'],
                    'email' => $supabaseUser['email'],
                    'name' => $supabaseUser['name'],
                    'preferred_weather' => is_array($supabaseUser['preferred_weather']) 
                        ? json_encode($supabaseUser['preferred_weather']) 
                        : $supabaseUser['preferred_weather']
                ]);
            }
        } catch (PDOException $e) {
            // Don't throw error for sync operations
            error_log('Failed to sync user to local database: ' . $e->getMessage());
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
