<?php

namespace app\commands;

use Throwable;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\datafeed\DatafeedService;
use app\components\datafeed\FeedFileRepo;
use app\components\platform\PlatformRepo;
use yii\console\Controller;
use yii\helpers\BaseConsole;

/**
 * This controller is used to update feed.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FeedFileController extends Controller
{
    /**
     * @var string
     */
    public $feed;

    /**
     * Declared options.
     *
     * @param string $actionID
     * @return string[]
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'feed',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'f' => 'feed',
        ]);
    }

    /**
     * Update all active feed.
     *
     * @param FeedFileRepo $feedFileRepo
     * @param FileRepo $fileRepo
     * @param PlatformRepo $platformRepo
     * @param ClientRepo $clientRepo
     * @param DatafeedService $datafeedService
     * @return void
     */
    public function actionUpdateAll(FeedFileRepo $feedFileRepo, FileRepo $fileRepo, PlatformRepo $platformRepo, ClientRepo $clientRepo, DatafeedService $datafeedService)
    {
        $feedFiles = $feedFileRepo->find()->where(['status' => '1'])->all();

        try {
            $client = null;
            $platform = null;
            foreach ($feedFiles as $feedFile) {
                echo 'Processing feed file: '.$feedFile['id']."\n";

                if (null == $client || $client['id'] !== $feedFile['client_id']) {
                    $client = $clientRepo->find()->where(['id' => $feedFile['client_id']])->one();
                }
                if (null == $platform || $platform['id'] !== $feedFile['platform_id']) {
                    $platform = $platformRepo->find()->where(['id' => $feedFile['platform_id']])->one();
                }

                $resultPath = $datafeedService->export($platform, $client, $feedFile);

                $data = [
                    'mime' => 'text/csv',
                    'extension' => 'csv',
                    'filename' => basename($resultPath),
                    'path' => $resultPath,
                    'size' => filesize($resultPath),
                ];

                $file = $fileRepo->create($data);

                $feedFileParams = [
                    'file_id' => $file['id'],
                ];

                $feedFile = $feedFileRepo->update($feedFile, $feedFileParams);

                echo 'Feed file: '.$feedFile['id'].' has been updated'."\n";
            }
            echo $this->ansiFormat("Successfully update all feeds\n", BaseConsole::BG_GREEN);
        } catch (Throwable $e) {
            echo 'Error: '.$e->getMessage()."\n";
        }
    }

    /**
     * Update feed by id, Only active feed is allowed.
     *
     * @param FeedFileRepo $feedFileRepo
     * @param FileRepo $fileRepo
     * @param PlatformRepo $platformRepo
     * @param ClientRepo $clientRepo
     * @param DatafeedService $datafeedService
     * @return void
     */
    public function actionUpdateFeed(FeedFileRepo $feedFileRepo, FileRepo $fileRepo, PlatformRepo $platformRepo, ClientRepo $clientRepo, DatafeedService $datafeedService)
    {
        try {
            $feedFile = $feedFileRepo->findOne(['id' => $this->feed, 'status' => '1']);
            $client = $clientRepo->find()->where(['id' => $feedFile['client_id']])->one();
            $platform = $platformRepo->find()->where(['id' => $feedFile['client_id']])->one();

            $resultPath = $datafeedService->export($platform, $client, $feedFile);

            $data = [
                'mime' => 'text/csv',
                'extension' => 'csv',
                'filename' => basename($resultPath),
                'path' => $resultPath,
                'size' => filesize($resultPath),
            ];

            $file = $fileRepo->create($data);

            $feedFileParams = [
                'file_id' => $file['id'],
            ];

            $feedFile = $feedFileRepo->update($feedFile, $feedFileParams);

            echo $this->ansiFormat('Successfully update feed:'.$feedFile['id']."\n", BaseConsole::BG_GREEN);
        } catch (Throwable $e) {
            echo 'Error: '.$e->getMessage()."\n";
        }
    }
}
