<?php

namespace app\components\enum;

use MyCLabs\Enum\Enum;

/**
 * Status enum of datafeed.
 *
 * @extends Enum<Action::*>
 */
final class DatafeedStatusEnum extends Enum
{
    /**
     * @var string
     */
    private const ACTIVE = '1';

    /**
     * @var string
     */
    private const INACTIVE = '0';
}
