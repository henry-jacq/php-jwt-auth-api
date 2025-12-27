<?php

namespace App;

class AuditLogger
{

    public static function log($db, $userId, $action)
    {
        $db->prepare(
            "INSERT INTO audit_logs (user_id, action, ip, user_agent)
             VALUES (?, ?, ?, ?)"
        )->execute([
            $userId,
            $action,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
