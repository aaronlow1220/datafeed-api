<?php

namespace app\components\datafeed;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\Datafeed;
use yii\db\ActiveQuery;

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

    /**
     * Find datafeed by client id.
     *
     * @param int $clientId
     *
     * @return ActiveQuery
     */
    public function findByClientId(int $clientId): ActiveQuery
    {
        return $this->find()->where(['client_id' => $clientId]);
    }
}
