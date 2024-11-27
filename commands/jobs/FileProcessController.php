<?php

namespace app\commands\jobs;

use app\components\version\DataVersionRepo;
use app\jobs\FileProcessJob;
use app\jobs\JobFactory;
use yii\console\Controller;
use yii\queue\Queue;

/**
 * This controller is used to update feed in database from file.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FileProcessController extends Controller
{
    /**
     * @var string
     */
    private $originalFilePath;

    public function __construct($id, $module, private DataVersionRepo $dataVersionRepo, private FileProcessJob $fileProcessJob, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->originalFilePath = __DIR__.'/../../runtime/files/original';
    }

    public function actionIndex(Queue $queue)
    {
        $data = $this->dataVersionRepo->find()->where(['status' => '2'])->all();

        echo 'Processing '.count($data)." files\n";

        foreach ($data as $item) {
            $job = JobFactory::createDatafeedJob([
                'clientId' => $item['client_id'],
                'filePath' => $this->originalFilePath.'/'.$item['filename'],
                'jobDataVersion' => $item['version'],
            ]);

            $queue->push($job);
        }
    }
}
