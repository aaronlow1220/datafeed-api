<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This trait declare relations of updater for active record.
 *
 * @author Calista Wu <calista.wu@atelli.ai>
 */
trait UserUpdaterTrait
{
    /**
     * declare relation of updater.
     *
     * @return null|ActiveQuery|array<string, mixed>
     */
    public function getUpdater(): null|ActiveQuery|array
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }
}
