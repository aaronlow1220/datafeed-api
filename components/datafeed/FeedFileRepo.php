<?php

namespace app\components\datafeed;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\FeedFile;

/**
 * This is a repository class for accessing feed file.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FeedFileRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = FeedFile::class;
}
