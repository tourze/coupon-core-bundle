<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 优惠券适用范围类型
 */
enum CouponScopeType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case ALL = 'all';
    case SKU = 'sku';
    case SPU = 'spu';
    case CATEGORY = 'category';

    public function getLabel(): string
    {
        return match ($this) {
            self::ALL => '全场商品',
            self::SKU => '指定SKU',
            self::SPU => '指定SPU',
            self::CATEGORY => '指定品类',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toSelectItems(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
            ];
        }

        return $result;
    }
}
