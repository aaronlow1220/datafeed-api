<?php

namespace app\components\core;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\File;
use yii\db\ActiveRecordInterface;

/**
 * This is a repository class for accessing Files.
 *
 * @author Noah Wang <noah.wang@atelli.ai>
 */
class FileRepo extends AbstractRepository
{
    /**
     * @var string the model class name. This property must be set.
     */
    protected string $modelClass = File::class;

    /**
     * Get files by array of ids.
     *
     * @param array<int> $arr
     * @return ActiveRecordInterface[]|array
     */
    public function getFiles(array $arr): array
    {
        return $this->find()->where(['in', 'id', $arr])->all();
    }
}
