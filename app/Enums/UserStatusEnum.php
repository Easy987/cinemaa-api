<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserStatusEnum extends Enum
{
    const Unverified =   0;
    const Verified =   1;
    const Blocked = 2;
}
