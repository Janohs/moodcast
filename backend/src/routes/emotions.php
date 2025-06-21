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
    
    $app->group('/api', function () use ($app) {
        $app->group('/emotions', function () use ($app) {
            // Create emotion entry
            $app->post('', [EmotionController::class, 'create']);
            
            // Get user's emotions
            $app->get('', [EmotionController::class, 'getUserEmotions']);
            
            // Get emotion insights
            $app->get('/insights', [EmotionController::class, 'getInsights']);
            
            // Delete emotion entry
            $app->delete('/{id}', [EmotionController::class, 'delete']);
        });
    });
};
