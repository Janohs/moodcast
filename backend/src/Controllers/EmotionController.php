<?php

namespace App\Controllers;

use App\Services\SupabaseService;
use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmotionController
{
    private $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    /**
     * Create a new emotion entry
     * POST /api/emotions
     */
    public function createEmotion(Request $request, Response $response, array $args): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['emotion_type']) || !isset($data['intensity'])) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Missing required fields: user_id, emotion_type, intensity'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validate intensity range
        if ($data['intensity'] < 1 || $data['intensity'] > 10) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Intensity must be between 1 and 10'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Create emotion in Supabase
            $supabaseResult = $this->supabaseService->createEmotion($data);
            
            if ($supabaseResult['success']) {
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'data' => $supabaseResult['data']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            } else {
                throw new \Exception($supabaseResult['error']);
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to create emotion: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Get user's emotions
     * GET /api/emotions?user_id=123&limit=30&days=7
     */
    public function getUserEmotions(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $userId = $queryParams['user_id'] ?? null;
        $limit = intval($queryParams['limit'] ?? 30);
        $days = intval($queryParams['days'] ?? 30);

        if (!$userId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'user_id is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Get emotions from Supabase
            $supabaseResult = $this->supabaseService->getUserEmotions($userId, $limit, $days);
            
            if ($supabaseResult['success']) {
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'data' => $supabaseResult['data']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                throw new \Exception($supabaseResult['error']);
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to get emotions: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Get emotion insights/analytics
     * GET /api/emotions/insights?user_id=123&days=30
     */
    public function getEmotionInsights(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $userId = $queryParams['user_id'] ?? null;
        $days = intval($queryParams['days'] ?? 30);

        if (!$userId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'user_id is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Get insights from Supabase
            $supabaseResult = $this->supabaseService->getEmotionInsights($userId, $days);
            
            if ($supabaseResult['success']) {
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'data' => $supabaseResult['data']
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                throw new \Exception($supabaseResult['error']);
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to get insights: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Delete an emotion entry
     * DELETE /api/emotions/{id}
     */
    public function deleteEmotion(Request $request, Response $response, array $args): Response
    {
        $emotionId = $args['id'] ?? null;

        if (!$emotionId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Emotion ID is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Try Supabase first
            $supabaseResult = $this->supabaseService->deleteEmotion($emotionId);
            
            if ($supabaseResult['success']) {
                // Also delete from SQLite
                $this->databaseService->deleteEmotion($emotionId);
                
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Emotion deleted successfully'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                // Fallback to SQLite
                $sqliteResult = $this->databaseService->deleteEmotion($emotionId);
                
                if ($sqliteResult['success']) {
                    $response->getBody()->write(json_encode([
                        'status' => 'success',
                        'message' => 'Emotion deleted successfully',
                        'source' => 'fallback'
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Exception($sqliteResult['error']);
                }
            }
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to delete emotion: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
