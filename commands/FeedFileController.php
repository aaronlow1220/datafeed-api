<?php

namespace app\commands;

use Exception;
use Hug\Sftp\Sftp;
use Throwable;
use Yii;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\datafeed\DatafeedService;
use app\components\datafeed\FeedFileRepo;
use app\components\platform\PlatformRepo;
use yii\console\Controller;
use yii\db\ActiveRecord;
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
        $sftp = Yii::$app->params['sftp'];

        $feedFiles = $feedFileRepo->find()->where(['status' => '1'])->all();

        try {
            $client = null;
            $platform = null;
            foreach ($feedFiles as $feedFile) {
                /**
                 * @var ActiveRecord $feedFile
                 */
                echo 'Processing feed file: '.$feedFile['id']."\n";

                if (null == $client || $client['id'] !== $feedFile['client_id']) {
                    /**
                     * @var ActiveRecord $client
                     */
                    $client = $clientRepo->find()->where(['id' => $feedFile['client_id']])->one();
                }
                if (null == $platform || $platform['id'] !== $feedFile['platform_id']) {
                    /**
                     * @var ActiveRecord $platform
                     */
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

                if ('1' == $platform['sftp']) {
                    $sftpFilePath = sprintf('ftp/files/%s_%s_%s_feed.csv', $client['name'], $platform['name'], $feedFile['id']);
                    $sftpUpload = Sftp::upload($sftp['host'], $sftp['username'], $sftp['password'], $resultPath, $sftpFilePath);
                    if (!$sftpUpload) {
                        throw new Exception('Failed to upload file to SFTP');
                    }
                }

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
            if (null == $this->feed) {
                echo $this->ansiFormat('Please provide feed id with "-f" flag'."\n", BaseConsole::BG_RED);

                return;
            }

            $feedFile = $feedFileRepo->findOne(['id' => $this->feed, 'status' => '1']);
            $client = $clientRepo->find()->where(['id' => $feedFile['client_id']])->one();
            $platform = $platformRepo->find()->where(['id' => $feedFile['platform_id']])->one();

            /**
             * @var ActiveRecord $client
             * @var ActiveRecord $platform
             */
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
