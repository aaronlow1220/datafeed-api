<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This trait declare relations of creator for active record.
 *
 * @author Calista Wu <calista.wu@atelli.ai>
 */
trait UserCreatorTrait
{
    /**
     * declare relation of creator.
     *
     * @return null|ActiveQuery|array<string, mixed>
     */
    public function getCreator(): null|ActiveQuery|array
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
