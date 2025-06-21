<?php

use App\Controllers\AuthController;
use Slim\App;

return function (App $app) {
    // Auth routes (no middleware needed)
    $app->group('/api/auth', function ($group) {
        $group->post('/register', [AuthController::class, 'register']);
        $group->post('/login', [AuthController::class, 'login']);
        $group->post('/me', [AuthController::class, 'me']);
        $group->put('/preferences', [AuthController::class, 'updatePreferences']);
    });
};
