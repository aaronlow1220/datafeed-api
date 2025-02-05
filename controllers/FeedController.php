<?php

namespace app\controllers;

use Throwable;
use Yii;
use app\components\client\ClientRepo;
use app\components\core\FileRepo;
use app\components\datafeed\FeedFileRepo;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * This controller is used to handle the feed related requests.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FeedController extends Controller
{
    public function actionIndex(string $client, int $id, string $pw, ClientRepo $clientRepo, FeedFileRepo $feedFileRepo, FileRepo $fileRepo, ?string $type = 'csv')
    {
        try {
            $client = $clientRepo->findOne(['name' => $client]);
            $feedFile = $feedFileRepo->findOne($id);

            if ($feedFile['client_id'] !== $client['id']) {
                throw new HttpException(400, 'Invalid client');
            }

            if (!Yii::$app->getSecurity()->validatePassword($pw, $client['password'])) {
                throw new HttpException(400, 'Invalid password');
            }

            $file = $fileRepo->findOne($feedFile['file_id']);

            if (!file_exists($file['path'])) {
                throw new HttpException(400, 'File not found');
            }

            return Yii::$app->response->sendFile($file['path']);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
