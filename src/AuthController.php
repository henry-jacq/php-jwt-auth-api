<?php

namespace App;

use PDO;

class AuthController
{
    public static function register($db, $config)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            exit(json_encode(['error' => 'Email and password are required']));
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$data['email']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            http_response_code(409);  // Conflict
            exit(json_encode(['error' => 'Email already registered']));
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$data['email'], $hashedPassword, 'user']);  // Default role set as user

        $userId = $db->lastInsertId();
        $user = [
            'id' => $userId,
            'email' => $data['email'],
            'role' => 'user'
        ];

        AuditLogger::log($db, $userId, 'register');

        echo json_encode([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }

    public static function login($db, $config)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            exit(json_encode(['error' => 'Invalid credentials']));
        }

        $accessToken = JwtService::generateAccessToken($user, $config['jwt']);
        $refreshToken = bin2hex(random_bytes(64));

        $db->prepare(
            "INSERT INTO refresh_tokens (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))"
        )->execute([
            $user['id'],
            password_hash($refreshToken, PASSWORD_BCRYPT)
        ]);

        AuditLogger::log($db, $user['id'], 'login');

        echo json_encode([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ]);
    }

    public static function logout($db, $jwt)
    {
        $db->prepare(
            "INSERT INTO token_blacklist VALUES (?, FROM_UNIXTIME(?))"
        )->execute([$jwt->jti, $jwt->exp]);

        AuditLogger::log($db, $jwt->sub, 'logout');

        echo json_encode(['message' => 'Logged out']);
    }
}
