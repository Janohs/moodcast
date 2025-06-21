<?php

namespace App\Services;

use PDO;
use PDOException;

class DatabaseService
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            // For now, we'll use SQLite since we're primarily using Supabase
            // This is just for local JWT authentication until we fully integrate
            $dbPath = dirname(__DIR__, 2) . '/storage/moodcast.db';
            $dbDir = dirname($dbPath);
            
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $this->connection = new PDO("sqlite:$dbPath");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $this->initializeTables();

        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseService();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function initializeTables()
    {
        // Create users table if it doesn't exist
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                preferred_weather TEXT DEFAULT '{}',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->connection->exec($sql);

        // Create emotions table if it doesn't exist
        $sql = "
            CREATE TABLE IF NOT EXISTS emotions (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                weather_log_id TEXT,
                emotion_type TEXT NOT NULL,
                intensity INTEGER CHECK (intensity >= 1 AND intensity <= 10),
                notes TEXT,
                weather_liked INTEGER DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";

        $this->connection->exec($sql);
    }

    private function __clone() {}
    public function __wakeup() {}
}
