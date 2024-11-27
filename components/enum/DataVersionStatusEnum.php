<?php

namespace app\components\enum;

use MyCLabs\Enum\Enum;

/**
 * Status enum of data version.
 *
 * @extends Enum<Action::*>
 */
final class DataVersionStatusEnum extends Enum
{
    /**
     * @var string
     */
    private const PROCESSING = '3';

    /**
     * @var string
     */
    private const PENDING = '2';

    /**
     * @var string
     */
    private const SUCCESS = '1';

    /**
     * @var string
     */
    private const FAILED = '0';
}
