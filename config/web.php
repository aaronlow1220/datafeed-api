<?php

use yii\web\Response;

$params = require __DIR__.'/params.php';
$urlManager = require __DIR__.'/urlManager.php';
$container = require __DIR__.'/container.php';

$config = [
    'id' => 'morph-api-v1',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'Asia/Taipei',
    'bootstrap' => ['log', 'v1', 'queue'],
    'aliases' => [
        '@v1' => '@app/modules/v1',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
            'controllerNamespace' => 'v1\controllers',
        ],
    ],
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => $params['db']['dsn'],
            'username' => $params['db']['username'],
            'password' => $params['db']['password'],
            'charset' => 'utf8mb4',
        ],
        'queue' => [
            'class' => '\yii\queue\db\Queue',
            'db' => 'db',
            'tableName' => 'queue',
            'channel' => 'default',
            'mutex' => '\yii\mutex\MysqlMutex',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // uncomment if you want to cache RBAC items hierarchy
            'cache' => 'cache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'AtelliTech\Yii2\Utils\Log\JsonFileLogTarget',
                    'levels' => ['error', 'warning'],
                    'enableRotation' => false,
                    'logFile' => '@runtime/logs/web.log',
                    'maskVars' => ['_SERVER.LS_COLORS', '_SERVER.SHELL', '_SERVER.PWD', '_SERVER.LOGNAME', '_SERVER.XDG_SESSION_TYPE', '_SERVER.MOTD_SHOWN', '_SERVER.HOME', '_SERVER.LANG', '_SERVER.SSH_CONNECTION', '_SERVER.LESSCLOSE', '_SERVER.XDG_SESSION_CLASS', '_SERVER.TERM', '_SERVER.LESSOPEN', '_SERVER.USER', '_SERVER.DISPLAY', '_SERVER.SHLVL', '_SERVER.XDG_SESSION_ID', '_SERVER.XDG_RUNTIME_DIR', '_SERVER.SSH_CLIENT', '_SERVER.XDG_DATA_DIRS', '_SERVER.PATH', '_SERVER.DBUS_SESSION_BUS_ADDRESS', '_SERVER.SSH_TTY', '_SERVER.OLDPWD', '_SERVER._', '_SERVER.PHP_SELF', '_SERVER.SCRIPT_NAME', '_SERVER.SCRIPT_FILENAME', '_SERVER.PATH_TRANSLATED', '_SERVER.DOCUMENT_ROOT'],
                ],
            ],
        ],
        'urlManager' => &$urlManager,
        'user' => [
            'class' => 'app\components\auth\User',
            'identityClass' => 'app\components\auth\UserIdentity',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'autoRenewCookie' => false,
        ],
    ],
    'container' => &$container,
    'params' => &$params,
];

return $config;
