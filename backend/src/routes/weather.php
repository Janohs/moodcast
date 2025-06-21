<?php

use App\Controllers\WeatherController;
use Slim\App;

return function (App $app) {
    // Weather API routes
    $app->group('/api/weather', function ($group) {
        $group->get('/current', [WeatherController::class, 'getCurrentWeather']);
        $group->get('/forecast', [WeatherController::class, 'getForecast']);
        $group->get('/search', [WeatherController::class, 'searchLocations']);
    });
};
