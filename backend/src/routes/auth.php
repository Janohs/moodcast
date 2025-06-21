<?php

use App\Controllers\AuthController;
use App\Middleware\JWTMiddleware;
use Slim\App;

return function (App $app) {
    // Auth routes (no middleware needed)
    $app->group('/api/auth', function ($group) {
        $group->post('/register', [AuthController::class, 'register']);
        $group->post('/login', [AuthController::class, 'login']);
        
        // Protected routes (require JWT)
        $group->get('/me', [AuthController::class, 'me'])->add(JWTMiddleware::class);
        $group->put('/preferences', [AuthController::class, 'updatePreferences'])->add(JWTMiddleware::class);
    });
};
