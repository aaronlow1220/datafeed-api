<?php

namespace app\jobs;

use Exception;
use Throwable;
use app\components\client\ClientRepo;
use app\components\datafeed\DatafeedRepo;
use app\components\datafeed\DatafeedService;
use app\components\version\DataVersionRepo;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FileProcessJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $clientId;

    /**
     * @var string
     */
    public $filePath;

    /**
     * @var string
     */
    public $jobDataVersion;

    /**
     * DatasetService constructor.
     *
     * @param DatafeedRepo $datafeedRepo
     * @param DataVersionRepo $dataVersionRepo
     * @param DatafeedService $datafeedService
     * @param ClientRepo $clientRepo
     * @param array<string, mixed> $config
     */
    public function __construct(private DatafeedRepo $datafeedRepo, private DataVersionRepo $dataVersionRepo, private DatafeedService $datafeedService, private ClientRepo $clientRepo, array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Execute the job.
     *
     * @param Queue $queue
     * @throws Throwable
     * @return void
     */
    public function execute($queue)
    {
        ini_set('memory_limit', '1000M');
        $version = date('YmdHis');
        $file = null;
        $transaction = $this->datafeedRepo->getDb()->beginTransaction();
        $dataVersion = $this->dataVersionRepo->find()->where(['client_id' => $this->clientId])->one();
        $client = $this->clientRepo->findOne($this->clientId);
        $transformedFilePath = $this->datafeedService->transformDataToFile($this->filePath, $client);

        try {
            if (!$dataVersion) {
                throw new Exception('Data version not found');
            }

            $dataVersion = $this->dataVersionRepo->update($dataVersion, ['status' => '3']);

            $clientDatafeeds = $this->datafeedRepo->findByClientId($this->clientId)->all();
            $hasExistingDatafeeds = count($clientDatafeeds) > 0;
            $file = fopen($transformedFilePath, 'r');
            $headers = fgetcsv($file);
            $processedDatafeedIds = [];

            // Read all datafeed from file into a separate array with datafeedid as key
            $fileDatafeeds = [];
            while (($row = fgetcsv($file)) !== false) {
                $record = array_combine($headers, $row);
                $fileDatafeeds[$record['datafeedid']] = $record;
            }

            if ($hasExistingDatafeeds) {
                foreach ($clientDatafeeds as $datafeed) {
                    $record = $fileDatafeeds[$datafeed['datafeedid']] ?? null;
                    if ($record) {
                        $record['status'] = '1';
                        $record['client_id'] = strval($this->clientId);
                        $record['version'] = $version;
                        $this->datafeedRepo->update($datafeed['id'], $record);
                    } else {
                        $this->datafeedRepo->update($datafeed['id'], ['status' => '0']);
                    }
                }
            } else {
                foreach ($fileDatafeeds as $record) {
                    $record['status'] = '1';
                    $record['client_id'] = strval($this->clientId);
                    $record['version'] = $version;
                    $this->datafeedRepo->create($record);
                }
            }

            $validDatafeeds = $this->datafeedRepo->find()->where(['version' => $version, 'client_id' => $this->clientId]);
            $validateCount = $validDatafeeds->count();

            if ($validateCount !== count($fileDatafeeds)) {
                throw new Exception('Datafeed count mismatch');
            }

            $transaction->commit();
            fclose($file);
            unlink($transformedFilePath);
            $this->dataVersionRepo->update($dataVersion, ['status' => '1']);

            return;
        } catch (Throwable $e) {
            if ($dataVersion) {
                $dataVersion = $this->dataVersionRepo->update($dataVersion, ['status' => '0']);
            }

            $transaction->rollBack();
            if ($file) {
                fclose($file);
                unlink($transformedFilePath);
            }

            throw $e;
        }
    }
}
