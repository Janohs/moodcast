<?php

require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Controllers\AuthController;
use App\Models\User;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

// Create mock request for testing registration
function testRegistration() {
    echo "Testing Registration...\n";
    
    $userModel = new User();
    $authController = new AuthController($userModel);
    
    // Create test data
    $testData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'weather_preferences' => ['sunny', 'cloudy']
    ];
    
    // Create mock request
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, json_encode($testData));
    rewind($stream);
    
    $uri = new Uri('http', 'localhost', 8000, '/api/auth/register');
    $request = new Request('POST', $uri);
    $request = $request->withBody(new Stream($stream));
    
    $response = new Response();
    
    try {
        $result = $authController->register($request, $response);
        $body = $result->getBody()->getContents();
        echo "Registration Response: " . $body . "\n\n";
    } catch (Exception $e) {
        echo "Registration Error: " . $e->getMessage() . "\n\n";
    }
}

// Create mock request for testing login
function testLogin() {
    echo "Testing Login...\n";
    
    $userModel = new User();
    $authController = new AuthController($userModel);
    
    // Create test data
    $testData = [
        'email' => 'test@example.com',
        'password' => 'password123'
    ];
    
    // Create mock request
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, json_encode($testData));
    rewind($stream);
    
    $request = new Request('POST', new Uri('/api/auth/login'));
    $request = $request->withBody(new Stream($stream));
    
    $response = new Response();
    
    try {
        $result = $authController->login($request, $response);
        $body = $result->getBody()->getContents();
        echo "Login Response: " . $body . "\n\n";
    } catch (Exception $e) {
        echo "Login Error: " . $e->getMessage() . "\n\n";
    }
}

// Create mock request for testing profile
function testProfile() {
    echo "Testing Profile (me endpoint)...\n";
    
    $userModel = new User();
    $authController = new AuthController($userModel);
    
    // Create test data
    $testData = [
        'email' => 'test@example.com',
        'password' => 'password123'
    ];
    
    // Create mock request
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, json_encode($testData));
    rewind($stream);
    
    $request = new Request('POST', new Uri('/api/auth/me'));
    $request = $request->withBody(new Stream($stream));
    
    $response = new Response();
    
    try {
        $result = $authController->me($request, $response);
        $body = $result->getBody()->getContents();
        echo "Profile Response: " . $body . "\n\n";
    } catch (Exception $e) {
        echo "Profile Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Authentication Test (No JWT) ===\n\n";

// Note: These tests will fall back to local SQLite if Supabase is not configured properly
testRegistration();
testLogin();
testProfile();

echo "=== Test Complete ===\n";
echo "Note: If Supabase credentials are not configured, the system will fall back to local SQLite database.\n";
