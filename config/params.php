<?php

return [
    'baseUrl' => $_ENV['BASE_URL'],
    'db' => [
        'dsn' => $_ENV['DB_DSN'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
    'auth' => [
        'clientId' => $_ENV['AUTH_CLIENT_ID'],
        'clientSecret' => $_ENV['AUTH_CLIENT_SECRET'],
    ],
    'sftp' => [
        'host' => $_ENV['SFTP_HOST'],
        'username' => $_ENV['SFTP_USERNAME'],
        'password' => $_ENV['SFTP_PASSWORD'],
    ],
];
