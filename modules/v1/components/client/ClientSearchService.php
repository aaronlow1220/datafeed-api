<?php

namespace v1\components\client;

use InvalidArgumentException;
use app\components\client\ClientRepo;
use v1\models\validator\ClientSearch;
use yii\data\ActiveDataProvider;

/**
 * Client search service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class ClientSearchService
{
    /**
     * constructor.
     *
     * @param ClientRepo $clientRepo
     */
    public function __construct(private ClientRepo $clientRepo) {}

    /**
     * create search data provider.
     *
     * @param array{keyword?:string, enabledValues?:string[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $query = null;
        $searchModel = new ClientSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        // create query
        $query = $this->clientRepo->find();

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
