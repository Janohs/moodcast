<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * User registration
     * POST /api/auth/register
     */
    public function register(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            // Validation
            $validation = $this->validateRegistration($data);
            if (!$validation['valid']) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Check if user already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'User with this email already exists'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }

            // Create user
            $user = $this->userModel->create($data);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'weather_preferences' => $user['weather_preferences'] ?? []
                    ]
                ]
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * User login
     * POST /api/auth/login
     */
    public function login(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            // Validation
            if (!isset($data['email']) || !isset($data['password'])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Email and password are required'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Find user
            $user = $this->userModel->findByEmail($data['email']);
            if (!$user) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            // Verify password
            if (!$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            // Return user data
            $userResponse = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'weather_preferences' => $user['weather_preferences'] ?? []
            ];

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $userResponse
                ]
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Login failed: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Get current user profile
     * POST /api/auth/me (expects email and password for simple auth)
     */
    public function me(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            // Validation
            if (!isset($data['email']) || !isset($data['password'])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Email and password are required'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Find user
            $user = $this->userModel->findByEmail($data['email']);
            if (!$user) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            // Verify password
            if (!$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'weather_preferences' => $user['weather_preferences'] ?? []
                    ]
                ]
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to get user profile: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Update weather preferences
     * PUT /api/auth/preferences (expects email, password, and preferences)
     */
    public function updatePreferences(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            // Validation
            if (!isset($data['email']) || !isset($data['password'])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Email and password are required'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Find user
            $user = $this->userModel->findByEmail($data['email']);
            if (!$user) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            // Verify password
            if (!$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            $this->userModel->updatePreferences($user['id'], $data['preferences'] ?? []);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Preferences updated successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to update preferences: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Validate registration data
     */
    private function validateRegistration($data)
    {
        $errors = [];

        // Name validation
        if (!isset($data['name']) || empty(trim($data['name']))) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        // Email validation
        if (!isset($data['email']) || empty(trim($data['email']))) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Password validation
        if (!isset($data['password']) || empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters long';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
