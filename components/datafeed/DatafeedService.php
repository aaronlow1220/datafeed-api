<?php

namespace app\components\datafeed;

use Exception;

use function Flow\ETL\Adapter\CSV\to_csv;
use function Flow\ETL\DSL\data_frame;
use function Flow\ETL\DSL\from_array;
use function Flow\ETL\DSL\to_array;

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
    /**
     * DatasetService constructor.
     *
     * @param DatafeedRepo $datafeedRepo
     * @param DataVersionRepo $dataVersionRepo
     */
    public function __construct(private DatafeedRepo $datafeedRepo, private DataVersionRepo $dataVersionRepo) {}

    /**
     * Create datafeed.
     *
     * @param ActiveRecord $client
     * @param array<int, mixed> $data
     *
     * @return void
     */
    public function create(ActiveRecord $client, array $data)
    {
        $transaction = $this->datafeedRepo->getDb()->beginTransaction();
        $clientDatafeed = $this->datafeedRepo->findByClientId($client['id'])->all();

        // Create a map of existing datafeeds
        $existingDatafeeds = [];
        foreach ($clientDatafeed as $datafeed) {
            $existingDatafeeds[$datafeed['datafeedid']] = $datafeed;
        }

        try {
            $newDatafeedIds = [];
            foreach ($data as $value) {
                $value['client_id'] = $client['id'];
                $datafeedId = $value['datafeedid'];
                $newDatafeedIds[] = $datafeedId;

                if (isset($existingDatafeeds[$datafeedId])) {
                    $this->datafeedRepo->update($existingDatafeeds[$datafeedId]['id'], $value);
                } else {
                    $this->datafeedRepo->create($value);
                }
            }

            // Update status to 0 for datafeeds not in the new data array
            foreach ($existingDatafeeds as $datafeedId => $datafeed) {
                if (!in_array($datafeedId, $newDatafeedIds)) {
                    $this->datafeedRepo->update($datafeed['id'], ['status' => 0]);
                }
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * Create datafeed from file.
     *
     * @param ActiveRecord $client
     * @param string $filePath
     *
     * @return array<int, mixed>
     */
    public function createFromFile(ActiveRecord $client, string $filePath): array
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

            $processedData = $this->transformDataFromFile($filePath, $client);

            $finalDataVersion = $this->dataVersionRepo->findByClientId($client['id'])->one();

            if ($initialDataVersion['version'] !== $finalDataVersion['version']) {
                throw new Exception('Data version not match', 400);
            }

            $this->create($client, $processedData);

            $dataVersion = [
                'filename' => basename($filePath),
                'hash' => hash_file('md5', $filePath),
                'version' => $finalDataVersion['version'] + 1,
            ];

            $this->dataVersionRepo->update($finalDataVersion, $dataVersion);

            return $processedData;
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
     * @return array<int, mixed>
     */
    public function transformDataFromFile(string $filePath, ActiveRecord $client): array
    {
        try {
            $fileType = $this->getFileExtension($filePath);

            $data = [];

            switch ($fileType) {
                case 'xml':
                    $data = $this->readXml($filePath);

                    break;

                case 'txt':
                case 'csv':
                    $data = $this->readCsv($filePath);

                    break;

                default:
                    throw new Exception('File type not supported.');
            }

            return $this->transform($data, $client);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Transform the data from the file.
     *
     * @param array<int, mixed> $data
     * @param ActiveRecord $client
     *
     * @return array<int, mixed>
     */
    public function transform(array $data, ActiveRecord $client): array
    {
        try {
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

            $processedData = [];

            // Unset empty values and non-existent keys in data from clientInfo and select
            foreach ($clientInfo as $key => $value) {
                if ('' === $value || !array_key_exists($value, $data[0])) {
                    unset($clientInfo[$key], $select[$key]);
                    // unset($select[$key]);
                }
            }

            $etl = data_frame()
                ->read(from_array($data));

            // Rename columns
            foreach ($clientInfo as $key => $value) {
                $etl->rename($value, $key);
            }

            $etl->select(...$select);

            $etl->load(to_array($processedData))->run();

            return $processedData;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Export the data to a file.
     *
     * @param ActiveRecord $platform
     * @param ActiveRecord $client
     * @param string $resultPath
     * @param array<string, mixed> $filter
     *
     * @return string
     */
    public function export(ActiveRecord $platform, ActiveRecord $client, string $resultPath, array $filter): string
    {
        $data = [];
        unset($filter['id'], $filter['platformid']);

        try {
            $datafeeds = $this->datafeedRepo->findByClientId($client['id'])->andWhere($filter)->all();

            foreach ($datafeeds as $datafeed) {
                $data[] = $datafeed['attributes'];
            }

            if (!$data) {
                throw new Exception('Datafeed not found', 400);
            }

            $platformInfo = json_decode($platform['data'], true);

            foreach ($platform as $key => $value) {
                if ('' === $value) {
                    unset($platform[$key]);
                }
            }

            $etl = data_frame()
                ->read(from_array($data));

            // Select only the columns that are required by the platform
            $etl->select(...array_keys($platformInfo));

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
     * @return array<int, mixed>
     */
    public function readXml(string $filePath): array
    {
        $data = [];
        $xml = new SimpleXMLElement($filePath, 0, true);

        foreach ($xml->channel->item as $item) {
            $itemData = [];
            foreach ($item->children('g', true) as $key => $value) {
                $itemData[$key] = trim((string) $value);
            }
            $data[] = $itemData;
        }

        return $data;
    }

    /**
     * Read CSV file.
     *
     * @param string $filePath
     *
     * @return array<int, mixed>
     */
    public function readCsv(string $filePath): array
    {
        $data = [];
        $file = fopen($filePath, 'r');

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

        return $data;
    }

    /**
     * Read feed file.
     *
     * @param string $directoryPath
     *
     * @return string
     */
    public function readFeedFile(string $directoryPath): string
    {
        try {
            $filePath = null;
            $files = scandir($directoryPath);
            foreach ($files as $file) {
                if (false !== strpos($file, '_feed')) {
                    $filePath = $directoryPath.'/'.$file;

                    break;
                }
            }

            if (!$filePath) {
                throw new Exception('File not found');
            }

            return $filePath;
        } catch (Throwable $e) {
            throw $e;
        }
    }

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
}
