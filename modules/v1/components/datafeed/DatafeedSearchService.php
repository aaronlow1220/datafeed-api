<?php

namespace v1\components\datafeed;

use InvalidArgumentException;
use app\components\datafeed\DatafeedRepo;
use v1\models\validator\DatafeedSearch;
use yii\data\ActiveDataProvider;

/**
 * Datafeed search service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class DatafeedSearchService
{
    /**
     * constructor.
     *
     * @param DatafeedRepo $datafeedRepo
     */
    public function __construct(private DatafeedRepo $datafeedRepo) {}

    /**
     * create search data provider.
     *
     * @param array{keyword?:string, enabledValues?:string[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $searchModel = new DatafeedSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        // create query
        $query = $this->datafeedRepo->find();

        // filter by keyword
        if ($searchModel->keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'family_name', $searchModel->keyword],
                ['like', 'given_name', $searchModel->keyword],
                ['like', 'email', $searchModel->keyword],
                ['like', 'sub', $searchModel->keyword],
            ]);
        }

        // filter by status values
        if ($searchModel->statusValues) {
            $query->andFilterWhere(['status' => $searchModel->statusValues]);
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
