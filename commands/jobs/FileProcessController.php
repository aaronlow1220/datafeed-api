<?php

namespace app\commands\jobs;

use app\components\version\DataVersionRepo;
use app\jobs\JobFactory;
use yii\base\Module;
use yii\console\Controller;
use yii\helpers\BaseConsole;
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

    /**
     * Summary of __construct.
     *
     * @param string $id
     * @param Module $module
     * @param DataVersionRepo $dataVersionRepo
     * @param array<string, mixed> $config
     * @return void
     */
    public function __construct($id, $module, private DataVersionRepo $dataVersionRepo, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->originalFilePath = __DIR__.'/../../runtime/files/original';
    }

    /**
     * Push all files to queue.
     *
     * @param Queue $queue
     * @return void
     */
    public function actionIndex(Queue $queue)
    {
        $data = $this->dataVersionRepo->find()->where(['status' => '2'])->all();

        echo 'Pushing '.count($data)." files to queue\n";

        foreach ($data as $item) {
            $job = JobFactory::createDatafeedJob([
                'clientId' => $item['client_id'],
                'filePath' => $this->originalFilePath.'/'.$item['filename'],
                'jobDataVersion' => $item['version'],
            ]);

            $queue->push($job);
        }

        echo $this->ansiFormat("All files are pushed to queue\n", BaseConsole::BG_GREEN);
    }
}
