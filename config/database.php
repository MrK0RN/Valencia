<?php

return [
    'default' => 'pgsql',
    
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'postgres',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_NAME'] ?? 'realestate',
            'username' => $_ENV['DB_USER'] ?? 'app_user',
            'password' => $_ENV['DB_PASSWORD'] ?? 'app_password',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],
    ],
];

