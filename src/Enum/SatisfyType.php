<?php

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 使用需要满足条件
 */
enum SatisfyType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case ORDER_MONEY_GT = 'order-money-gt';
    case ORDER_MONEY_LT = 'order-money-lt';
    case INCLUDE_SPU_CATEGORY = 'include-spu-category';
    case INCLUDE_SPU = 'include-spu';
    case INCLUDE_SKU = 'include-sku';
    case GATHER_DAY_GT = 'gather-day-gt';

    public function getLabel(): string
    {
        return match ($this) {
            self::ORDER_MONEY_GT => '整单总金额大于',
            self::ORDER_MONEY_LT => '整单总金额小于',
            self::INCLUDE_SPU_CATEGORY => '包含指定品类',
            self::INCLUDE_SPU => '包含指定SPU',
            self::INCLUDE_SKU => '包含指定SKU',
            self::GATHER_DAY_GT => '领取天数大于',
        };
    }
}
