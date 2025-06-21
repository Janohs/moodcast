<?php

namespace App\Middleware;

use App\Services\JWTService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JWTMiddleware
{
    private $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Authorization header missing'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = $this->jwtService->extractTokenFromHeader($authHeader);
        
        if (!$token) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid authorization header format'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $verification = $this->jwtService->verifyToken($token);
        
        if (!$verification['valid']) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid or expired token'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Add user data to request attributes
        $request = $request->withAttribute('userId', $verification['data']['userId']);
        $request = $request->withAttribute('userEmail', $verification['data']['email']);
        $request = $request->withAttribute('userName', $verification['data']['name']);

        return $handler->handle($request);
    }
}
