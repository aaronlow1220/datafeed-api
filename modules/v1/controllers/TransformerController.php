<?php

namespace v1\controllers;

use v1\components\ActiveApiController;
use yii\web\HttpException;
use Throwable;
use SimpleXMLElement;
use app\modules\v1\Module;
use app\components\transformer\TransformerService;
use app\components\client\ClientRepo;
use app\components\platform\PlatformRepo;

class TransformerController extends ActiveApiController
{
    /**
     * constructor.
     *
     * @param string $id
     * @param Module $module
     * @param TransformerService $transformerService
     * @param ClientRepo $clientRepo
     * @param PlatformRepo $platformRepo
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function __construct(string $id, Module $module, private TransformerService $transformerService, private ClientRepo $clientRepo, private PlatformRepo $platformRepo, array $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * Transform the data from the file.
     *
     * @return string
     */
    public function actionTransform(string $client, string $platform): array
    {
        try {
            $filePath = __DIR__ . '/../files/original/airspace_feed.csv';
            $clientInfo = $this->clientRepo->findOne(['name' => $client]);
            $platformInfo = $this->platformRepo->findOne(['name' => $platform]);

            if (!file_exists($filePath)) {
                throw new HttpException(400, 'File does not exist.');
            }

            $fileInfo = pathinfo($filePath);

            if (!isset($fileInfo['extension'])) {
                throw new HttpException(400, 'File does not have an extension.');
            }

            $fileType = $fileInfo['extension'];

            $data = [];

            switch ($fileType) {
                case 'xml':
                    $xml = new SimpleXMLElement($filePath, 0, true);

                    foreach ($xml->channel->item as $item) {
                        $itemData = [];
                        foreach ($item->children('g', true) as $key => $value) {
                            $itemData[$key] = trim((string) $value);
                        }
                        $data[] = $itemData;
                    }
                    $this->transformerService->transform($data, $clientInfo, $platformInfo);
                    break;
                case 'txt':
                case 'csv':
                    $file = fopen($filePath, "r");

                    // Remove BOM
                    $bom = fread($file, 3);

                    // Read the first line to get the column headers
                    $headers = fgetcsv($file);

                    // Loop through the rest of the file to get the data
                    while (($row = fgetcsv($file)) !== false) {
                        // Combine headers with corresponding row data
                        $data[] = array_combine($headers, $row);
                    }

                    // Close the file
                    fclose($file);

                    $this->transformerService->transform($data, $clientInfo, $platformInfo);
                    break;
                default:
                    throw new HttpException(400, 'File type not supported.');
            }
        } catch (Throwable $e) {
            throw new HttpException(400, $e->getMessage());
        }
        
        return $data;
    }
}
