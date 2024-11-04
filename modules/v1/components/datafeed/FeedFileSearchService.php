<?php

namespace v1\components\datafeed;

use InvalidArgumentException;
use app\components\datafeed\FeedFileRepo;
use v1\models\validator\FeedFileSearch;
use yii\data\ActiveDataProvider;

/**
 * FeedFile search service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FeedFileSearchService
{
    /**
     * constructor.
     *
     * @param FeedFileRepo $feedFileRepo
     */
    public function __construct(private FeedFileRepo $feedFileRepo) {}

    /**
     * create search data provider.
     *
     * @param array{keyword?:string, enabledValues?:string[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $searchModel = new FeedFileSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        // create query
        $query = $this->feedFileRepo->find();

        // filter by keyword
        if ($searchModel->keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'utm', $searchModel->keyword],
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
