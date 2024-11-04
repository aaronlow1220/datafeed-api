<?php

namespace app\components\enum;

use MyCLabs\Enum\Enum;

/**
 * Status enum of feed_file.
 *
 * @extends Enum<Action::*>
 */
final class FeedFileStatusEnum extends Enum
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
