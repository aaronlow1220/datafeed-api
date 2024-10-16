<?php

namespace app\components\core;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\Taxonomy;
use yii\db\ActiveQuery;

/**
 * This is a repository class for accessing Taxonomy.
 */
class TaxonomyRepo extends AbstractRepository
{
    /**
     * @var string the model class name. This property must be set.
     */
    protected string $modelClass = Taxonomy::class;

    /**
     * find taxonomy by type id.
     *
     * @param int $typeId
     * @return ActiveQuery
     */
    public function findByTypeId(int $typeId): ActiveQuery
    {
        return $this->find()->where(['type_id' => $typeId]);
    }
}
