<?php

return [
    'baseUrl' => $params['baseUrl'],
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => array_merge(
        [
            'GET <a:(heartbeat|login|profile)>' => 'site/<a>',
            'GET auth/<socialType:(google)>' => 'auth/login',
            'GET auth/callback' => 'auth/callback',
        ],
        require __DIR__.'/routes/v1.php',
    ),
];
