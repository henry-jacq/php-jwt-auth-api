<?php

namespace App;

use PDO;


class Database
{
    private static $pdo;

    public static function connect($config)
    {
        if (!self::$pdo) {
            self::$pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['name']}",
                $config['user'],
                $config['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$pdo;
    }
}
