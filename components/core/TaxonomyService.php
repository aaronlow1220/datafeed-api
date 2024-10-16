<?php

namespace app\components\core;

use yii\db\ActiveQuery;

/**
 * This service is used to handle specific business logic for Taxonomy.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
final class TaxonomyService
{
    /**
     * construct.
     *
     * @param TaxonomyRepo $taxonomyRepo
     * @param TaxonomyTypeRepo $taxonomyTypeRepo
     * @return void
     */
    public function __construct(
        private TaxonomyRepo $taxonomyRepo,
        private TaxonomyTypeRepo $taxonomyTypeRepo,
    ) {}

    /**
     * create search query.
     *
     * @param array{keyword?: string, typeIds?: int[], typeNames?: string[]} $params
     * @return ActiveQuery
     */
    public function createSearchQuery(array $params): ActiveQuery
    {
        $query = $this->taxonomyRepo->find();

        $keyword = $params['keyword'] ?? null;
        if (null !== $keyword) {
            $query->andWhere([
                'or',
                ['like', 'value', $keyword],
                ['like', 'label', $keyword],
                ['like', 'description', $keyword],
            ]);
        }

        $typeIds = $params['typeIds'] ?? null;
        if (null !== $typeIds && is_array($typeIds)) {
            $query->andWhere(['type_id' => $typeIds]);
        }

        $typeNames = $params['typeNames'] ?? null;
        if (null !== $typeNames) {
            $typeQuery = $this->taxonomyTypeRepo->findByNames($typeNames)
                ->select(['id']);
            $query->andWhere(['type_id' => $typeQuery]);
        }

        return $query;
    }
}
