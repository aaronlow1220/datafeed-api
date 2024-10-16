<?php

namespace v1\components\version;

use InvalidArgumentException;
use app\components\version\DataVersionRepo;
use v1\models\validator\DataVersionSearch;
use yii\data\ActiveDataProvider;

/**
 * Data Version search service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class DataVersionSearchService
{
    /**
     * constructor.
     *
     * @param DataVersionRepo $dataVersionRepo
     */
    public function __construct(private DataVersionRepo $dataVersionRepo) {}

    /**
     * create search data provider.
     *
     * @param array{keyword?:string, enabledValues?:string[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $query = null;
        $searchModel = new DataVersionSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        // create query
        $query = $this->dataVersionRepo->find();

        // filter by keyword
        if ($searchModel->keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $searchModel->keyword],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
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

        return $dataProvider;
    }
}
