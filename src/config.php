<?php

namespace App;

return [
    'jwt' => [
        'secret' => "0987654321-jwt-api-secret",
        'issuer' => 'api-auth',
        'access_expiry' => 900,      // 15 mins
        'refresh_expiry' => 604800   // 7 days
    ],
    'db' => [
        'host' => 'localhost',
        'name' => 'rest_db',
        'user' => '',
        'pass' => ''
    ]
];
