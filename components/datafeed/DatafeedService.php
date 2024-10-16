<?php

namespace app\components\datafeed;

use function Flow\ETL\Adapter\CSV\to_csv;
use function Flow\ETL\DSL\data_frame;
use function Flow\ETL\DSL\{from_array, to_array};

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
     * @param DatafeedRepo $datasetEventRepo
     */
    public function __construct(private DatafeedRepo $datafeedRepo)
    {
    }

    /**
     * Create datafeed.
     *
     * @param ActiveRecord $client
     * @param array $data
     *
     * @return void
     */
    public function create(ActiveRecord $client, array $data)
    {
        $transaction = $this->datafeedRepo->getDb()->beginTransaction();
        try {
            foreach ($data as $value) {
                $value['client_id'] = $client["id"];
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
     * @param ActiveRecord $platform
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
                $etl->renameAll($value, $key);
            }

            $etl->select(...$select);

            $etl->load(to_array($processedData))->run();

            return $processedData;

        } catch (Throwable $e) {
            throw $e;
        }
    }
}
