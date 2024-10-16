<?php

namespace v1\components\core;

use app\components\core\TaxonomyTypeRepo;
use v1\models\validator\TaxonomyTypeSearch;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;

/**
 * Taxonomy Type search service.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class TaxonomyTypeSearchService
{
    /**
     * construct.
     *
     * @param TaxonomyTypeRepo $taxonomyTypeRepo
     */
    public function __construct(private TaxonomyTypeRepo $taxonomyTypeRepo) {}

    /**
     * create data provider for TaxonomyType.
     *
     * @param array{keyword?:string} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $searchModel = new TaxonomyTypeSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        $query = $this->taxonomyTypeRepo->find();

        // filter keyword
        if ($searchModel->keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'description', $searchModel->keyword],
                ['like', 'name', $searchModel->keyword],
            ]);
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
