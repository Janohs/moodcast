<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService
{
    private $secretKey;
    private $algorithm;
    private $issuer;
    private $expirationTime;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-this-in-production';
        $this->algorithm = 'HS256';
        $this->issuer = 'moodcast-api';
        $this->expirationTime = 24 * 60 * 60; // 24 hours
    }

    /**
     * Generate JWT token for user
     */
    public function generateToken($user)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expirationTime;

        $payload = [
            'iss' => $this->issuer,
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => [
                'userId' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ]
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Verify and decode JWT token
     */
    public function verifyToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return [
                'valid' => true,
                'data' => (array) $decoded->data
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract token from Authorization header
     */
    public function extractTokenFromHeader($authHeader)
    {
        if (!$authHeader) {
            return null;
        }

        // Bearer <token>
        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return null;
        }

        return $parts[1];
    }

    /**
     * Generate refresh token (simple implementation)
     */
    public function generateRefreshToken($userId)
    {
        $payload = [
            'iss' => $this->issuer,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60), // 7 days
            'type' => 'refresh',
            'userId' => $userId
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }
}
