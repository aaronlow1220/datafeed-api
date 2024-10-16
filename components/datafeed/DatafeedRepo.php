<?php

namespace app\components\datafeed;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\Datafeed;

/**
 * This is a repository class for accessing datafeed.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class DatafeedRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = Datafeed::class;
}
