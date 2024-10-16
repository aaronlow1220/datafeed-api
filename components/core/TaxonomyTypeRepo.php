<?php

namespace app\components\core;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\TaxonomyType;
use yii\db\ActiveQuery;

/**
 * This is a repository class for accessing TaxonomyType.
 */
class TaxonomyTypeRepo extends AbstractRepository
{
    /**
     * @var string the model class name. This property must be set.
     */
    protected string $modelClass = TaxonomyType::class;

    /**
     * find by name.
     *
     * @param string $name
     * @return ActiveQuery
     */
    public function findByName(string $name): ActiveQuery
    {
        return $this->findByNames([$name]);
    }

    /**
     * find by names.
     *
     * @param string[] $names
     * @return ActiveQuery
     */
    public function findByNames(array $names): ActiveQuery
    {
        return $this->find()->andWhere(['name' => $names]);
    }
}
