<?php

namespace v1\components\datafeed;

use Exception;
use Throwable;
use app\components\datafeed\FeedFileRepo;
use yii\data\DataFilter;
use yii\db\ActiveRecord;

/**
 * Feed file update service.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FeedFileUpdateService
{
    /**
     * Construct.
     *
     * @param FeedFileRepo $feedFileRepo
     */
    public function __construct(private FeedFileRepo $feedFileRepo) {}

    /**
     * Update feed file.
     *
     * @param ActiveRecord $feedFile
     * @param array<string, mixed> $params
     * @return ActiveRecord
     */
    public function update($feedFile, $params)
    {
        $transaction = $this->feedFileRepo->getDb()->beginTransaction();

        try {
            $filterModel = new DataFilter([
                'searchModel' => 'v1\models\validator\DatafeedFilter',
            ]);

            $filter = json_decode($params['filter'], true);

            $filterModel->load($filter);

            if (!$filterModel->validate() && $filterModel->build()) {
                throw new Exception('Invalid filter condition');
            }

            // Check utm param, if '?' exist in any position, throw exception
            if (false !== strpos($params['utm'], '?')) {
                throw new Exception('Invalid utm parameter');
            }

            $feedFile = $this->feedFileRepo->update($feedFile, $params);

            $transaction->commit();

            return $feedFile;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }
}
