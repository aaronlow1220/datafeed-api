<?php

return [
    'GET /apidoc' => 'v1/open-api-spec/index',
    [ // Client
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'client' => 'v1/client',
        ],
        'except' => ['delete'],
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
        'except' => ['delete'],
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
    [ // Datafeed
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'datafeed' => 'v1/datafeed',
        ],
        'extraPatterns' => [
            'OPTIONS <a:(search)>' => 'options',
            'OPTIONS <a:(export)>/<id:\d+>/<platformid:\d+>' => 'options',
            'POST <a:(search)>' => '<a>',
            'GET <a:(export)>/<id:\d+>/<platformid:\d+>' => '<a>',
        ],
    ],
    [ // File
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'file' => 'v1/file',
        ],
        'extraPatterns' => [
            'OPTIONS <a:(upload)>/<id:\d+>' => 'options',
            'OPTIONS <a:(feed)>/<filename>' => 'options',
            'POST <a:(upload)>/<id:\d+>' => '<a>',
            'GET <a:(feed)>/<filename>' => '<a>',
        ],
    ],
];
