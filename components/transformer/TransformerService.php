<?php

namespace app\components\transformer;

use function Flow\ETL\Adapter\CSV\to_csv;
use function Flow\ETL\DSL\data_frame;
use function Flow\ETL\DSL\from_array;

use Throwable;
use yii\db\ActiveRecord;

/**
 * This is a service to handle business logic for transformer.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class TransformerService
{
    /**
     * Transform the data from the file.
     *
     * @param string $resultPath
     * @param array<int, mixed> $data
     * @param ActiveRecord $client
     * @param ActiveRecord $platform
     *
     * @return void
     */
    public function transform(string $resultPath, array $data, ActiveRecord $client, ActiveRecord $platform): void
    {
        try {
            // $destinationPath = __DIR__ . '/../../modules/v1/files/result/ ' . $client['name'] . '_adgeek_feed.csv';
            $client = json_decode($client['data'], true);
            $platform = json_decode($platform['data'], true);

            foreach ($platform as $key => $value) {
                if ('' === $value || '' === $client[$key]) {
                    unset($client[$key]);
                }
            }

            $etl = data_frame()
                ->read(from_array($data));

            // Rename columns
            foreach ($client as $key => $value) {
                $etl->rename($value, $key);
            }

            // Select only the columns that are required by the platform
            $etl->select(...array_keys($platform));

            // Load to CSV
            $etl->load(to_csv($resultPath))->run();
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
