<?php

namespace app\components\enum;

use MyCLabs\Enum\Enum;

/**
 * Enum of if price and sale price need to append currenct.
 *
 * @extends Enum<Action::*>
 */
final class PlatformPriceCurrencyEnum extends Enum
{
    /**
     * @var string
     */
    private const TRUE = '1';

    /**
     * @var string
     */
    private const FALSE = '0';
}
