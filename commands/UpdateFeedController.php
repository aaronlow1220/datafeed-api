<?php

namespace app\commands;

use Exception;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\datafeed\DatafeedService;
use app\components\datafeed\FeedFileRepo;
use app\components\platform\PlatformRepo;
use yii\console\Controller;
use yii\helpers\BaseConsole;

class UpdateFeedController extends Controller
{
    /**
     * Declared options.
     *
     * @param string $actionID
     * @return string[]
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
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
        $resultPath = __DIR__.'/../runtime/files/result';
        $feedFiles = $feedFileRepo->find()->where(['status' => '1'])->all();

        try {
            if (!is_dir($resultPath)) {
                mkdir($resultPath, 0777, true);
            }

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

                $filePath = sprintf('%s/%s_%s_%s_feed.csv', $resultPath, uniqid(), $client['name'], $platform['name']);

                $datafeedService->export($platform, $client, $feedFile, $filePath);

                $data = [
                    'mime' => 'text/csv',
                    'extension' => 'csv',
                    'filename' => basename($filePath),
                    'path' => $filePath,
                    'size' => filesize($filePath),
                ];

                $file = $fileRepo->create($data);

                $feedFileParams = [
                    'file_id' => $file['id'],
                ];

                $feedFile = $feedFileRepo->update($feedFile, $feedFileParams);

                echo 'Feed file: '.$feedFile['id'].' has been updated'."\n";
            }
            echo $this->ansiFormat("Successfully update all feeds\n", BaseConsole::BG_GREEN);
        } catch (Exception $e) {
            echo 'Error: '.$e->getMessage()."\n";
        }
    }
}
