<?php

namespace app\components\transformer;

use Throwable;
use yii\db\ActiveRecord;
use function Flow\ETL\DSL\{data_frame, from_array};
use function Flow\ETL\Adapter\CSV\{to_csv};

/**
 * This is a service to handle business logic for transformer.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class TransformerService
{
    public function transform(string $resultPath, array $data, ActiveRecord $client, ActiveRecord $platform): void
    {
        try {
            // $destinationPath = __DIR__ . '/../../modules/v1/files/result/ ' . $client['name'] . '_adgeek_feed.csv';
            $client = json_decode($client["data"], true);
            $platform = json_decode($platform["data"], true);

            foreach ($platform as $key => $value) {
                if ($value === '' || $client[$key] === '') {
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