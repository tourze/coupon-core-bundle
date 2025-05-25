<?php

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 条件场景枚举
 */
enum ConditionScenario: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case REQUIREMENT = 'requirement';  // 领取条件
    case SATISFY = 'satisfy';          // 使用条件

    public function getLabel(): string
    {
        return match ($this) {
            self::REQUIREMENT => '领取条件',
            self::SATISFY => '使用条件',
        };
    }
}
