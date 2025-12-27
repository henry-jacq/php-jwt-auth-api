<?php

namespace App;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{

    public static function generateAccessToken($user, $config)
    {
        $payload = [
            'iss' => $config['issuer'],
            'iat' => time(),
            'exp' => time() + $config['access_expiry'],
            'sub' => $user['id'],
            'role' => $user['role'],
            'jti' => bin2hex(random_bytes(16))
        ];
        return JWT::encode($payload, $config['secret'], 'HS256');
    }

    public static function verify($token, $config)
    {
        if (substr_count($token, '.') !== 2) {
            http_response_code(401);
            exit(json_encode(['error' => 'Malformed token']));
        }

        return JWT::decode($token, new Key($config['secret'], 'HS256'));
    }
}
