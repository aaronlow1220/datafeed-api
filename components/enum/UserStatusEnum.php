<?php

namespace app\components\enum;

use MyCLabs\Enum\Enum;

/**
 * Status enum of user.
 *
 * @extends Enum<Action::*>
 */
final class UserStatusEnum extends Enum
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
