<?php

namespace App;

use Exception;


class Middleware
{

    public static function enforceHttps()
    {
        if (empty($_SERVER['HTTPS'])) {
            http_response_code(403);
            exit(json_encode(['error' => 'HTTPS required']));
        }
    }

    public static function auth($db, $config, $requiredRole = null)
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            exit(json_encode(['error' => 'Missing Authorization header']));
        }

        $token = substr($authHeader, 7);

        if (substr_count($token, '.') !== 2) {
            http_response_code(401);
            exit(json_encode(['error' => 'Malformed token']));
        }

        try {
            $decoded = JwtService::verify($token, $config['jwt']);
        } catch (Exception $e) {
            http_response_code(401);
            exit(json_encode(['error' => 'Invalid or expired token']));
        }

        // blacklist check
        $stmt = $db->prepare("SELECT 1 FROM token_blacklist WHERE jti=?");
        $stmt->execute([$decoded->jti]);
        if ($stmt->fetch()) {
            http_response_code(401);
            exit(json_encode(['error' => 'Token revoked']));
        }

        if ($requiredRole && $decoded->role !== $requiredRole) {
            http_response_code(403);
            exit(json_encode(['error' => 'Forbidden']));
        }

        return $decoded;
    }
}
