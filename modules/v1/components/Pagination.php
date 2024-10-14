<?php

namespace v1\components;

/**
 * This is a pagination component extends \yii\data\Pagination, its change pageSizeParam from per-page into pageSize.
 */
class Pagination extends \yii\data\Pagination
{
    /**
     * @var string
     */
    public $pageSizeParam = 'pageSize';
}
