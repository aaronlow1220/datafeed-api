<?php

namespace app\components\version;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\DataVersion;
use yii\db\ActiveQuery;

/**
 * This is a repository class for accessing data version.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class DataVersionRepo extends AbstractRepository
{
    /**
     * @var string Model class. Required.
     */
    protected string $modelClass = DataVersion::class;

    /**
     * Find data version by client id.
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
