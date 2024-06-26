<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class StatusEnum extends Enum
{
    const Pending = 0;
    const Active =  1;
    const Unlisted = 2;
    const Filtered = 3;
}
