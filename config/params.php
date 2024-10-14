<?php

return [
    'baseUrl' => $_ENV['BASE_URL'],
    'db' => [
        'dsn' => $_ENV['DB_DSN'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
];
