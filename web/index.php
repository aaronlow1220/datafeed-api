<?php

require __DIR__.'/../vendor/autoload.php';

$repository = Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(Dotenv\Repository\Adapter\EnvConstAdapter::class)
    ->addWriter(Dotenv\Repository\Adapter\PutenvAdapter::class)
    ->immutable()
    ->make();

$dotenv = Dotenv\Dotenv::create($repository, dirname(__DIR__), '.env');
$dotenv->safeLoad();

defined('YII_DEBUG') or define('YII_DEBUG', 'true' == getenv('YII_DEBUG'));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV'));

require __DIR__.'/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__.'/../config/web.php';

(new yii\web\Application($config))->run();
