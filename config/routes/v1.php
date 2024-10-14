<?php

return [
    'GET /apidoc' => 'v1/open-api-spec/index',
    [ // taxonomy-type
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'taxonomy-type' => 'v1/taxonomy-type',
        ],
        'except' => ['index', 'delete'],
        'extraPatterns' => [
            'OPTIONS <a:(search)>' => 'options',
            'POST <a:(search)>' => '<a>',
        ],
    ],
];
