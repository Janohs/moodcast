<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use App\Services\WeatherService;
use App\Services\JWTService;
use App\Services\DatabaseService;
use App\Services\SupabaseService;
use App\Controllers\WeatherController;
use App\Controllers\AuthController;
use App\Controllers\EmotionController;
use App\Models\User;
use App\Middleware\JWTMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();

// Configure dependencies
$container->set(WeatherService::class, function () {
    return new WeatherService();
});

$container->set(WeatherController::class, function ($container) {
    return new WeatherController($container->get(WeatherService::class));
});

// Auth dependencies
$container->set(SupabaseService::class, function () {
    return new SupabaseService();
});

$container->set(JWTService::class, function () {
    return new JWTService();
});

$container->set(User::class, function () {
    return new User();
});

$container->set(AuthController::class, function ($container) {
    return new AuthController(
        $container->get(User::class)
    );
});

$container->set(DatabaseService::class, function () {
    return DatabaseService::getInstance();
});

$container->set(EmotionController::class, function ($container) {
    return new EmotionController(
        $container->get(SupabaseService::class)
    );
});

$container->set(JWTMiddleware::class, function ($container) {
    return new JWTMiddleware($container->get(JWTService::class));
});

// Create App with container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['CORS_ORIGIN'] ?? '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Handle preflight requests
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

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

// Include weather routes
$weatherRoutes = require __DIR__ . '/../src/routes/weather.php';
$weatherRoutes($app);

// Include auth routes
$authRoutes = require __DIR__ . '/../src/routes/auth.php';
$authRoutes($app);

// Include emotion routes
$emotionRoutes = require __DIR__ . '/../src/routes/emotions.php';
$emotionRoutes($app);

$app->run();
