<?php

namespace app\components\core;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\File;

/**
 * This is a repository class for accessing file.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FileRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = File::class;
}
