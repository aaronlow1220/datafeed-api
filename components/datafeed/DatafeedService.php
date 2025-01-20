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
        $this->cachePath = __DIR__.'/../../runtime/files/cache';
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
                    $cachedDataPath = $this->readTxt($filePath);

                    break;

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
            $tempFilePath = sprintf('%s/%s.csv', $this->cachePath, uniqid());
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
        $filterModel = new ActiveDataFilter([
            'searchModel' => 'v1\models\validator\DatafeedFilter',
        ]);

        $filter = json_decode($feedFile['filter'], true);

        if (!($filterModel->load($filter) && $filterModel->validate())) {
            throw new Exception('Invalid filter condition');
        }

        $filterCondition = $filterModel->build();

        try {
            $datafeeds = $this->datafeedRepo->findByClientId($client['id']);

            if (null !== $filterCondition) {
                $datafeeds = $datafeeds->andWhere($filterCondition);
            }

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
                    foreach ($feed as $key => $value) {
                        if ('object' === gettype($feed[$key])) {
                            if ('DOMDocument' === get_class($feed[$key])) {
                                $feed[$key] = $feed[$key]->textContent;
                            }
                        }
                    }

                    if (null !== $feed['price']) {
                        $feed['price'] = sprintf('%s %s', $feed['price'], $client['currency']);
                    }
                    if (null !== $feed['sale_price']) {
                        $feed['sale_price'] = sprintf('%s %s', $feed['sale_price'], $client['currency']);
                    }
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
        try {
            $xml = new SimpleXMLElement($filePath, 0, true);
            $outputCsvPath = sprintf('%s/%s.csv', $this->cachePath, uniqid());
            $csvFile = fopen($outputCsvPath, 'w');

            $headerWritten = false;

            foreach ($xml->channel->item as $item) {
                $itemData = [];
                foreach ($item->children('g', true) as $key => $value) {
                    $itemData[$key] = preg_replace('/\s+/', ' ', (string) $value);
                }

                if (!$headerWritten) {
                    fputcsv($csvFile, array_keys($itemData));
                    $headerWritten = true;
                }

                fputcsv($csvFile, array_values($itemData));
            }

            fclose($csvFile);

            return $outputCsvPath;
        } catch (Throwable $e) {
            throw $e;
        }
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
        $file = fopen($filePath, 'r');
        $tempFilePath = sprintf('%s/%s.csv', $this->cachePath, uniqid());

        $bom = fread($file, 3);
        if ("\xEF\xBB\xBF" === $bom) {
            $content = fread($file, filesize($filePath) - 3);

            file_put_contents($tempFilePath, $content);
        } else {
            copy($filePath, $tempFilePath);
        }

        fclose($file);

        return $tempFilePath;
    }

    /**
     * Read TXT file.
     *
     * @param string $filePath
     *
     * @return string
     */
    public function readTxt(string $filePath): string
    {
        $tempFilePath = sprintf('%s/%s.csv', $this->cachePath, uniqid());
        $handle = fopen($filePath, 'r');
        $output = fopen($tempFilePath, 'w');

        $firstLine = fgets($handle);
        $separator = strpos($firstLine, ',') ? ',' : "\t";

        fputcsv($output, str_getcsv($firstLine, $separator));

        while (($line = fgetcsv($handle, 0, $separator)) !== false) {
            fputcsv($output, $line);
        }

        fclose($handle);
        fclose($output);

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
        if (false !== strpos($utmParam, '?')) {
            throw new Exception('UTM query should not contain ?');
        }

        foreach ($data as $key => $value) {
            $linkParamConnector = strpos($data[$key]['link'], '?') ? '&' : '?';
            if (!empty($data[$key]['link'])) {
                $data[$key]['link'] = $value['link'].$linkParamConnector.$utmParam;
            }
        }

        return $data;
    }
}
