<?php

namespace v1\components\platform;

use InvalidArgumentException;
use app\components\platform\PlatformRepo;
use v1\models\validator\PlatformSearch;
use yii\data\ActiveDataProvider;

/**
 * Platform search service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class PlatformSearchService
{
    /**
     * constructor.
     *
     * @param PlatformRepo $platformRepo
     */
    public function __construct(private PlatformRepo $platformRepo) {}

    /**
     * create search data provider.
     *
     * @param array{keyword?:string, enabledValues?:string[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $query = null;
        $searchModel = new PlatformSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        // create query
        $query = $this->platformRepo->find();

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
