<?php

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 可能不太需要这枚举了
 */
enum CodeStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case UNUSED = 'unused';
    case USED = 'used';
    case INVALID = 'invalid';
    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::UNUSED => '未使用',
            self::USED => '已使用',
            self::INVALID => '无效',
            self::EXPIRED => '已过期',
        };
    }
}
