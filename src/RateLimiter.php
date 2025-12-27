<?php

namespace App;

use PDO;


class RateLimiter
{

    public static function check($db, $ip, $endpoint, $limit = 10)
    {
        $stmt = $db->prepare("SELECT * FROM rate_limits WHERE ip=? AND endpoint=?");
        $stmt->execute([$ip, $endpoint]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && strtotime($row['expires_at']) > time()) {
            if ($row['hits'] >= $limit) {
                http_response_code(429);
                exit(json_encode(['error' => 'Too many requests']));
            }
            $db->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE ip=? AND endpoint=?")
                ->execute([$ip, $endpoint]);
        } else {
            $db->prepare("REPLACE INTO rate_limits VALUES (?, ?, 1, DATE_ADD(NOW(), INTERVAL 1 MINUTE))")
                ->execute([$ip, $endpoint]);
        }
    }
}
