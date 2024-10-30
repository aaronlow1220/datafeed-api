<?php

namespace v1\components\file;

use InvalidArgumentException;
use app\components\core\FileRepo;
use v1\models\validator\FileSearch;
use yii\data\ActiveDataProvider;

/**
 * File search service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FileSearchService
{
    /**
     * constructor.
     *
     * @param FileRepo $fileRepo
     */
    public function __construct(private FileRepo $fileRepo) {}

    /**
     * create search data provider.
     *
     * @param array{keyword?:string, enabledValues?:string[]} $params
     * @return ActiveDataProvider
     */
    public function createDataProvider(array $params): ActiveDataProvider
    {
        $searchModel = new FileSearch($params);
        if (!$searchModel->validate()) {
            throw new InvalidArgumentException(implode(' ', $searchModel->getErrorSummary(true)));
        }

        // create query
        $query = $this->fileRepo->find();

        // filter by keyword
        if ($searchModel->keyword) {
            $query->andFilterWhere([
                'or',
                ['like', 'filename', $searchModel->keyword],
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
