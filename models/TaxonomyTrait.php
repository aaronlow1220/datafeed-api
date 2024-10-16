<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This trait declare relations of taxonomy.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
trait TaxonomyTrait
{
    /**
     * get taxonomy by particular type name, table field, tax field.
     *
     * @param string $typeName
     * @param string $tableField
     * @param string $taxField [value, id] default: value
     * @return null|ActiveQuery|array<string, mixed>
     */
    public function getTaxonomy(string $typeName, string $tableField, string $taxField = 'value'): null|ActiveQuery|array
    {
        $taxTypeQuery = TaxonomyType::find()->select('id')
            ->where(['like', 'name', $typeName]);

        return $this->hasOne(Taxonomy::class, [$taxField => $tableField])
            ->andWhere(['type_id' => $taxTypeQuery]);
    }
}
