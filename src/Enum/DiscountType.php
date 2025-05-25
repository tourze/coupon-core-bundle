<?php

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum DiscountType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case FREIGHT = 'freight';
    case ORDER = 'order';

    public function getLabel(): string
    {
        return match ($this) {
            self::FREIGHT => '抵扣运费',
            self::ORDER => '整单抵扣',
        };
    }
}
