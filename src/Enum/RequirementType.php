<?php

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 领取条件
 */
enum RequirementType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case REG_DAY_LT = 'reg-day-lt';
    case REG_DAY_GT = 'reg-day-gt';
    case TOTAL_GATHER_COUNT = 'total-gather-count';

    public function getLabel(): string
    {
        return match ($this) {
            self::REG_DAY_LT => '注册天数小于',
            self::REG_DAY_GT => '注册天数大于',
            self::TOTAL_GATHER_COUNT => '该券领取上限',
        };
    }
}
