<?php

use App\Database;
use App\Middleware;
use App\AuthController;
use App\RateLimiter;

require 'vendor/autoload.php';

$config = require 'src/config.php';
$db = Database::connect($config['db']);

header('Content-Type: application/json');

// Middleware::enforceHttps();
RateLimiter::check($db, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);

$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($path === '/api/v1/auth/login' && $method === 'POST') {
    AuthController::login($db, $config);
}

if ($path === '/api/v1/auth/register' && $method === 'POST') {
    AuthController::register($db, $config);
}

if ($path === '/api/v1/profile' && $method === 'GET') {
    $user = Middleware::auth($db, $config);
    echo json_encode(['user_id' => $user->sub, 'role' => $user->role]);
}

if ($path === '/api/v1/auth/logout' && $method === 'POST') {
    $jwt = Middleware::auth($db, $config);
    AuthController::logout($db, $jwt);
}
