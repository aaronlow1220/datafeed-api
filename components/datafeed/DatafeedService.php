<?php

namespace app\components\datafeed;

use Exception;

use function Flow\ETL\Adapter\CSV\to_csv;
use function Flow\ETL\DSL\data_frame;
use function Flow\ETL\DSL\from_array;
use function Flow\ETL\DSL\to_array;

use SimpleXMLElement;
use Throwable;
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
     */
    public function __construct(private DatafeedRepo $datafeedRepo)
    {
    }

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

        try {
            foreach ($data as $value) {
                $value['client_id'] = $client['id'];
                $this->datafeedRepo->create($value);
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

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
                'datafeedid',
                'availability',
                'condition',
                'description',
                'image_link',
                'link',
                'title',
                'price',
                'sale_price',
                'gtin',
                'mpn',
                'brand',
                'google_product_category',
                'item_group_id',
                'custom_label_0',
                'custom_label_1',
                'custom_label_2',
                'custom_label_3',
                'custom_label_4',
            ];

            $processedData = [];

            // Unset empty values from $client
            foreach ($clientInfo as $key => $value) {
                if ('' === $value) {
                    unset($clientInfo[$key]);
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
     * @param array<int, mixed> $data
     * @param ActiveRecord $platform
     * @param ActiveRecord $client
     * @param string $resultPath
     *
     * @return void
     */
    public function export(array $data, ActiveRecord $platform, ActiveRecord $client, string $resultPath): void
    {
        $platform = json_decode($platform['data'], true);

        foreach ($platform as $key => $value) {
            if ('' === $value) {
                unset($platform[$key]);
            }
        }

        $etl = data_frame()
            ->read(from_array($data));

        // Select only the columns that are required by the platform
        $etl->select(...array_keys($platform));

        // Load to CSV
        $etl->load(to_csv($resultPath))->run();
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
            $fileDetected = false;
            foreach ($files as $file) {
                if (false !== strpos($file, '_feed')) {
                    $filePath = $directoryPath . $file;

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

            $fileType = $fileInfo['extension'];

            return $fileType;
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
