<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Basic route
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write('MoodCast API is running!');
    return $response;
});

// Health check endpoint
$app->get('/health', function (Request $request, Response $response, $args) {
    $data = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'slim_version' => '4.x'
    ];
    
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
