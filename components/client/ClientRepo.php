<?php

namespace app\components\client;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\Client;

/**
 * This is a repository class for accessing client.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class ClientRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = Client::class;
}
