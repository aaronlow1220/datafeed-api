<?php

namespace app\components\enum;

use MyCLabs\Enum\Enum;

/**
 * Enum of if need to send to sftp folder.
 *
 * @extends Enum<Action::*>
 */
final class PlatformSftpEnum extends Enum
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
