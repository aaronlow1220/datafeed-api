<?php

namespace app\components\datafeed;

use Exception;

use function Flow\ETL\Adapter\CSV\from_csv;
use function Flow\ETL\Adapter\CSV\to_csv;
use function Flow\ETL\DSL\data_frame;
use function Flow\ETL\DSL\from_array;
use function Flow\ETL\DSL\to_array;

use SimpleXMLElement;
use Throwable;
use yii\data\ActiveDataFilter;
use yii\db\ActiveRecord;

/**
 * This is a service to handle business logic for datafeed.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class DatafeedService
{
    /**
     * @var string
     */
    private string $resultPath;

    /**
     * @var string
     */
    private string $cachePath;

    /**
     * DatasetService constructor.
     *
     * @param DatafeedRepo $datafeedRepo
     */
    public function __construct(private DatafeedRepo $datafeedRepo)
    {
        $this->resultPath = __DIR__.'/../../runtime/files/result';
        $this->cachePath = __DIR__.'/../../runtime/cache';
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
            $tempFilePath = $this->cachePath.'/'.uniqid().'.csv';
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
        $filter = json_decode($feedFile['filter'], true);

        $filterModel = new ActiveDataFilter([
            'searchModel' => 'v1\models\validator\DatafeedFilter',
        ]);

        $filterCondition = null;
        if ($filterModel->load($filter)) {
            $filterCondition = $filterModel->build();
        }

        try {
            $datafeeds = $this->datafeedRepo->findByClientId($client['id'])->andWhere($filterCondition);
            $resultPath = sprintf('%s/%s_%s_%s_feed.csv', $this->resultPath, uniqid(), $client['name'], $platform['name']);

            $file = fopen($resultPath, 'w');
            $platformInfo = json_decode($platform['data'], true);
            foreach ($platformInfo as $key => $value) {
                if ('' === $value) {
                    unset($platformInfo[$key]);
                }
            }
            fputcsv($file, $platformInfo);

            foreach ($datafeeds->batch(6000) as $datafeed) {
                $data = [];
                foreach ($datafeed as $item) {
                    $data[] = $item['attributes'];
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

                $data = [];
                $etl->load(to_array($data))->run();
                foreach ($data as $feed) {
                    fputcsv($file, $feed);
                }
            }

            fclose($file);

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
        $outputCsvPath = $this->cachePath.'/'.uniqid().'.csv';
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
        $tempFilePath = $this->cachePath.'/'.uniqid().'.csv';

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
