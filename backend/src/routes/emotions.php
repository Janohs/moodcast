<?php

use App\Controllers\EmotionController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
    // Test route
    $app->get('/api/emotions/test', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode(['message' => 'Emotion routes working']));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Create emotion entry
    $app->post('/api/emotions', [EmotionController::class, 'createEmotion']);
    
    // Get user's emotions
    $app->get('/api/emotions', [EmotionController::class, 'getUserEmotions']);
    
    // Get emotion insights
    $app->get('/api/emotions/insights', [EmotionController::class, 'getEmotionInsights']);
    
    // Delete emotion entry
    $app->delete('/api/emotions/{id}', [EmotionController::class, 'deleteEmotion']);
};
