<?php

namespace v1\components\datafeed;

use Exception;
use Throwable;
use app\components\datafeed\FeedFileRepo;
use yii\data\ActiveDataFilter;
use yii\db\ActiveRecord;

/**
 * Feed file create service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FeedFileCreateService
{
    /**
     * Construct.
     *
     * @param FeedFileRepo $feedFileRepo
     */
    public function __construct(private FeedFileRepo $feedFileRepo) {}

    /**
     * Create feed file.
     *
     * @param array<string, mixed> $params
     * @return ActiveRecord
     */
    public function create($params)
    {
        $transaction = $this->feedFileRepo->getDb()->beginTransaction();

        try {
            $filterModel = new ActiveDataFilter([
                'searchModel' => 'v1\models\validator\DatafeedFilter',
            ]);

            $filter = json_decode($params['filter'], true);

            $filterModel->load($filter);

            if (!$filterModel->validate() && $filterModel->build()) {
                throw new Exception('Invalid filter condition');
            }

            if (false !== strpos($params['utm'], '?')) {
                throw new Exception('UTM query should not contain ?');
            }

            $feedFile = $this->feedFileRepo->create($params);

            $transaction->commit();

            return $feedFile;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }
}
