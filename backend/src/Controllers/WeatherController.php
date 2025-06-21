<?php

namespace App\Controllers;

use App\Services\WeatherService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class WeatherController
{
    private $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get current weather
     * GET /api/weather/current?location=London (optional, defaults to user's IP location)
     */
    public function getCurrentWeather(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $location = $queryParams['location'] ?? 'auto:ip';

        $result = $this->weatherService->getCurrentWeather($location);

        if ($result['success']) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $result['error']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Get weather forecast
     * GET /api/weather/forecast?location=London&days=3 (both optional)
     */
    public function getForecast(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $location = $queryParams['location'] ?? 'auto:ip';
        $days = (int)($queryParams['days'] ?? 3);

        $result = $this->weatherService->getForecast($location, $days);

        if ($result['success']) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $result['error']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Search for locations
     * GET /api/weather/search?q=London
     */
    public function searchLocations(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $query = $queryParams['q'] ?? '';

        if (empty($query)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Query parameter "q" is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $result = $this->weatherService->searchLocations($query);

        if ($result['success']) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $result['error']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
