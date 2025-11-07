<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 优惠券类型
 */
enum CouponType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case FULL_REDUCTION = 'full_reduction';
    case FULL_GIFT = 'full_gift';
    case REDEEM = 'redeem';
    case BUY_GIFT = 'buy_gift';

    public function getLabel(): string
    {
        return match ($this) {
            self::FULL_REDUCTION => '满减券',
            self::FULL_GIFT => '满赠券',
            self::REDEEM => '兑换券',
            self::BUY_GIFT => '买赠券',
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
