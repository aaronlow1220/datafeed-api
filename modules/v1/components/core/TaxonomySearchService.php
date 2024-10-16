<?php

namespace v1\components\core;

use app\components\core\TaxonomyRepo;
use app\components\core\TaxonomyTypeRepo;
use v1\models\validator\TaxonomySearch;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;

/**
 * Taxonomy search service.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class TaxonomySearchService
{
    /**
     * construct.
     *
     * @param TaxonomyTypeRepo $taxonomyTypeRepo
     * @param TaxonomyRepo $taxonomyRepo
     */
    public function __construct(
        protected TaxonomyTypeRepo $taxonomyTypeRepo,
        protected TaxonomyRepo $taxonomyRepo
    ) {}

    /**
     * create search query.
     *
     * @param array{keyword?:string, typeName?:string, typeIds?:int[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $searchModel = new TaxonomySearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        $query = $this->taxonomyRepo->find();

        // filter keyword
        if ($searchModel->keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'label', $searchModel->keyword],
                ['like', 'value', $searchModel->keyword],
            ]);
        }

        // filter type name
        if ($searchModel->typeName) {
            $typeQuery = $this->taxonomyTypeRepo->find()->where(['name' => $searchModel->typeName])
                ->select('id');

            $query->andFilterWhere(['in', 'type_id', $typeQuery]);
        }

        // filter type ids
        if ($searchModel->typeIds) {
            $query->andFilterWhere(['in', 'type_id', $searchModel->typeIds]);
        }

        // create data provider
        return new ActiveDataProvider([
            'query' => &$query,
            'pagination' => [
                'class' => 'v1\components\Pagination',
                'params' => $params,
            ],
            'sort' => [
                'enableMultiSort' => true,
                'params' => $params,
            ],
        ]);
    }
}
