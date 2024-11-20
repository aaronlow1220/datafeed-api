<?php

namespace app\components\datafeed;

use Exception;

use function Flow\ETL\Adapter\CSV\from_csv;
use function Flow\ETL\Adapter\CSV\to_csv;
use function Flow\ETL\DSL\data_frame;
use function Flow\ETL\DSL\from_array;

use SimpleXMLElement;
use Throwable;
use app\components\version\DataVersionRepo;
use yii\db\ActiveRecord;

/**
 * This is a service to handle business logic for datafeed.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class DatafeedService
{
    private string $destPath;

    /**
     * DatasetService constructor.
     *
     * @param DatafeedRepo $datafeedRepo
     * @param DataVersionRepo $dataVersionRepo
     */
    public function __construct(private DatafeedRepo $datafeedRepo, private DataVersionRepo $dataVersionRepo)
    {
        $this->destPath = __DIR__.'/../../runtime/files/result';
    }

    /**
     * Create datafeed.
     *
     * @param ActiveRecord $client
     * @param string $filePath
     *
     * @return string
     */
    public function createOrUpdateWithFile(ActiveRecord $client, string $filePath): string
    {
        set_time_limit(180);
        $transaction = $this->datafeedRepo->getDb()->beginTransaction();

        try {
            $feed = null;
            $file = null;
            $clientDatafeeds = $this->datafeedRepo->findByClientId($client['id']);
            $hasExistingDatafeeds = $clientDatafeeds->exists();
            $file = fopen($filePath, 'r');
            $headers = fgetcsv($file);
            $processedDatafeedIds = [];

            if ($hasExistingDatafeeds) {
                foreach ($clientDatafeeds->batch(6000) as $datafeeds) {
                    $existingDatafeedMap = [];
                    foreach ($datafeeds as $datafeed) {
                        $existingDatafeedMap[$datafeed['datafeedid']] = $datafeed;
                    }

                    while (($row = fgetcsv($file)) !== false) {
                        $isDifferent = false;
                        $record = array_combine($headers, $row);
                        $record['status'] = '1';
                        $record['client_id'] = strval($client['id']);

                        if (in_array($record['datafeedid'], $processedDatafeedIds)) {
                            continue;
                        }

                        if (isset($existingDatafeedMap[$record['datafeedid']])) {
                            $existingRecord = $existingDatafeedMap[$record['datafeedid']];
                            $isDifferent = $record != array_intersect_key($existingRecord->attributes, $record);

                            if ($isDifferent) {
                                $feed = $this->datafeedRepo->update($existingRecord, $record);
                            }
                            $processedDatafeedIds[] = $record['datafeedid'];
                        } else {
                            $feed = $this->datafeedRepo->create($record);
                            $processedDatafeedIds[] = $record['datafeedid'];
                        }
                    }
                    rewind($file);
                }

                $notExist = $clientDatafeeds->andWhere(['not in', 'datafeedid', $processedDatafeedIds])->all();
                foreach ($notExist as $missingDatafeed) {
                    $this->datafeedRepo->update($missingDatafeed, ['status' => '0']);
                }
            } else {
                while (($row = fgetcsv($file)) !== false) {
                    $record = array_combine($headers, $row);
                    $record['status'] = '1';
                    $record['client_id'] = strval($client['id']);
                    $feed = $this->datafeedRepo->create($record);

                    $processedDatafeedIds[] = $record['datafeedid'];
                }
            }

            $transaction->commit();
            fclose($file);

            return $filePath;
        } catch (Throwable $e) {
            $transaction->rollBack();
            fclose($file);

            throw $e;
        }
    }

    /**
     * Create datafeed from file.
     *
     * @param ActiveRecord $client
     * @param string $filePath
     *
     * @return void
     */
    public function createFromFile(ActiveRecord $client, string $filePath): void
    {
        try {
            $data = [];
            $initialDataVersion = $this->dataVersionRepo->findByClientId($client['id'])->one();

            if (!$initialDataVersion) {
                $dataVersion = [
                    'filename' => basename($filePath),
                    'hash' => hash_file('md5', $filePath),
                    'client_id' => $client['id'],
                    'version' => 0,
                ];
                $initialDataVersion = $this->dataVersionRepo->create($dataVersion);
            }

            $processedDataPath = $this->transformDataToFile($filePath, $client);

            $finalDataVersion = $this->dataVersionRepo->findByClientId($client['id'])->one();

            if ($initialDataVersion['version'] !== $finalDataVersion['version']) {
                throw new Exception('Data version not match', 400);
            }

            $datafeed = $this->createOrUpdateWithFile($client, $processedDataPath);

            $dataVersion = [
                'filename' => basename($filePath),
                'hash' => hash_file('md5', $filePath),
                'version' => $finalDataVersion['version'] + 1,
            ];

            $this->dataVersionRepo->update($finalDataVersion, $dataVersion);

            unlink($processedDataPath);
            unlink($filePath);

            return;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Transform the data from the file into database schema.
     *
     * @param string $filePath
     * @param ActiveRecord $client
     *
     * @return string
     */
    public function transformDataToFile(string $filePath, ActiveRecord $client): string
    {
        try {
            $fileType = $this->getFileExtension($filePath);
            $cachedDataPath = '';

            switch ($fileType) {
                case 'xml':
                    $cachedDataPath = $this->readXml($filePath);

                    break;

                case 'txt':
                case 'csv':
                    $cachedDataPath = $this->readCsv($filePath);

                    break;

                default:
                    throw new Exception('File type not supported.');
            }

            return $this->transform($cachedDataPath, $client);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Transform the data from the file.
     *
     * @param string $dataPath
     * @param ActiveRecord $client
     *
     * @return string
     */
    public function transform(string $dataPath, ActiveRecord $client): string
    {
        try {
            $tempFilePath = __DIR__.'/../../runtime/cache/'.uniqid().'.csv';
            $clientInfo = json_decode($client['data'], true);

            $select = [
                'datafeedid' => 'datafeedid',
                'availability' => 'availability',
                'condition' => 'condition',
                'description' => 'description',
                'image_link' => 'image_link',
                'link' => 'link',
                'title' => 'title',
                'price' => 'price',
                'sale_price' => 'sale_price',
                'gtin' => 'gtin',
                'mpn' => 'mpn',
                'brand' => 'brand',
                'google_product_category' => 'google_product_category',
                'item_group_id' => 'item_group_id',
                'custom_label_0' => 'custom_label_0',
                'custom_label_1' => 'custom_label_1',
                'custom_label_2' => 'custom_label_2',
                'custom_label_3' => 'custom_label_3',
                'custom_label_4' => 'custom_label_4',
            ];

            // get key of the file
            $header = fopen($dataPath, 'r');
            $header = fgetcsv($header);

            // Unset empty values and non-existent keys in data from clientInfo and select
            foreach ($clientInfo as $key => $value) {
                if ('' === $value || !in_array($value, $header)) {
                    unset($clientInfo[$key], $select[$key]);
                }
            }

            $etl = data_frame()
                ->read(from_csv($dataPath));

            // Rename columns
            foreach ($clientInfo as $key => $value) {
                $etl->rename($value, $key);
            }

            $etl->select(...$select);

            $etl->load(to_csv($tempFilePath))->run();

            unlink($dataPath);

            return $tempFilePath;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Export the data to a file.
     *
     * @param ActiveRecord $platform
     * @param ActiveRecord $client
     * @param ActiveRecord $feedFile
     *
     * @return string
     */
    public function export(ActiveRecord $platform, ActiveRecord $client, ActiveRecord $feedFile): string
    {
        $data = [];
        $filter = json_decode($feedFile['filter'], true);

        try {
            $datafeeds = $this->datafeedRepo->findByClientId($client['id'])->andWhere($filter)->all();
            $resultPath = sprintf('%s/%s_%s_%s_feed.csv', $this->destPath, uniqid(), $client['name'], $platform['name']);

            foreach ($datafeeds as $datafeed) {
                $data[] = $datafeed['attributes'];
            }

            if (!$data) {
                throw new Exception('Datafeed not found', 400);
            }

            $platformInfo = json_decode($platform['data'], true);

            foreach ($platformInfo as $key => $value) {
                if ('' === $value) {
                    unset($platformInfo[$key]);
                }
            }

            if (!empty($feedFile['utm'])) {
                $data = $this->addUtmParameters($data, $feedFile['utm']);
            }

            $etl = data_frame()
                ->read(from_array($data))
                ->select(...array_keys($platformInfo));

            foreach ($platformInfo as $key => $value) {
                $etl->rename($key, $value);
            }

            // Load to CSV
            $etl->load(to_csv($resultPath))->run();

            return $resultPath;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Read XML file.
     *
     * @param string $filePath
     *
     * @return string
     */
    public function readXml(string $filePath): string
    {
        $xml = new SimpleXMLElement($filePath, 0, true);
        $outputCsvPath = __DIR__.'/../../runtime/cache/'.uniqid().'.csv';
        $csvFile = fopen($outputCsvPath, 'w');

        $headerWritten = false;

        foreach ($xml->channel->item as $item) {
            $itemData = [];
            foreach ($item->children('g', true) as $key => $value) {
                $itemData[$key] = preg_replace('/\s+/', ' ', (string) $value);
            }

            // Write CSV header (once)
            if (!$headerWritten) {
                fputcsv($csvFile, array_keys($itemData));
                $headerWritten = true;
            }

            // Write data row to CSV
            fputcsv($csvFile, array_values($itemData));
        }

        fclose($csvFile);

        return $outputCsvPath;
    }

    /**
     * Read CSV file.
     *
     * @param string $filePath
     *
     * @return string
     */
    public function readCsv(string $filePath): string
    {
        // Open the original file
        $file = fopen($filePath, 'r');
        $tempFilePath = __DIR__.'/../../runtime/cache/'.uniqid().'.csv';

        // Detect BOM
        $bom = fread($file, 3);  // Read first 3 bytes
        if ("\xEF\xBB\xBF" === $bom) {
            // If BOM detected, remove it and get the rest of the content
            $content = fread($file, filesize($filePath) - 3);

            file_put_contents($tempFilePath, $content);  // Write content without BOM to the temp file
        } else {
            copy($filePath, $tempFilePath);  // Copy file to the temp file
        }

        // Close the original file
        fclose($file);

        // Return the path to the temporary file
        return $tempFilePath;
    }

    /**
     * Get file extension.
     *
     * @param string $filePath
     *
     * @return string
     */
    public function getFileExtension(string $filePath): string
    {
        try {
            $fileInfo = pathinfo($filePath);

            if (!isset($fileInfo['extension'])) {
                throw new Exception('File does not have an extension.');
            }

            return $fileInfo['extension'];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Add UTM parameters to the link.
     *
     * @param array<int, mixed> $data
     * @param string $utmParam
     *
     * @return array<int, mixed>
     */
    public function addUtmParameters(array $data, string $utmParam): array
    {
        foreach ($data as $key => $value) {
            $linkParamConnector = strpos($data[$key]['link'], '?') ? '&' : '?';
            if (!empty($data[$key]['link'])) {
                $data[$key]['link'] = $value['link'].$linkParamConnector.$utmParam;
            }
        }

        return $data;
    }
}
