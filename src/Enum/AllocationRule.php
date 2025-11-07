<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 优惠分摊规则
 */
enum AllocationRule: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case PROPORTIONAL = 'proportional';
    case AVERAGE = 'average';
    case PRIORITY = 'priority';

    public function getLabel(): string
    {
        return match ($this) {
            self::PROPORTIONAL => '按金额占比分摊',
            self::AVERAGE => '平均分摊',
            self::PRIORITY => '优先商品分摊',
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
