<?php

return [
    'GET /apidoc' => 'v1/open-api-spec/index',
    [ // Client
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'client' => 'v1/client',
        ],
        'except' => ['index', 'delete'],
        'extraPatterns' => [
            'OPTIONS <a:(search)>' => 'options',
            'POST <a:(search)>' => '<a>',
        ],
    ],
    [ // Platform
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'platform' => 'v1/platform',
        ],
        'except' => ['index', 'delete'],
        'extraPatterns' => [
            'OPTIONS <a:(search)>' => 'options',
            'POST <a:(search)>' => '<a>',
        ],
    ],
    [ // Data Version
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'data-version' => 'v1/data-version',
        ],
        'except' => ['index', 'delete'],
        'extraPatterns' => [
            'OPTIONS <a:(search)>' => 'options',
            'POST <a:(search)>' => '<a>',
        ],
    ],
    [ // Transformer
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'transformer' => 'v1/transformer',
        ],
        'except' => ['index', 'delete'],
        'extraPatterns' => [
            'OPTIONS <a:(search)>' => 'options',
            'POST <a:(search)>' => '<a>',
        ],
    ],
];
