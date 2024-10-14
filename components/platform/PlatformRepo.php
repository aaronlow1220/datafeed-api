<?php

namespace app\components\platform;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\Platform;

/**
 * This is a repository class for accessing platform.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class PlatformRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = Platform::class;
}
