<?php

namespace app\components\core;

use yii\db\ActiveQuery;

/**
 * This service is used to handle specific business logic for TaxonomyType.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
final class TaxonomyTypeService
{
    /**
     * construct.
     *
     * @param TaxonomyTypeRepo $taxonomyTypeRepo
     * @return void
     */
    public function __construct(
        private TaxonomyTypeRepo $taxonomyTypeRepo,
    ) {}

    /**
     * create search query.
     *
     * @param array{keyword?: string} $params
     * @return ActiveQuery
     */
    public function createSearchQuery(array $params): ActiveQuery
    {
        $query = $this->taxonomyTypeRepo->find();

        $keyword = $params['keyword'] ?? null;
        if (null !== $keyword) {
            $query->andWhere([
                'or',
                ['like', 'name', $keyword],
                ['like', 'description', $keyword],
            ]);
        }

        return $query;
    }
}
