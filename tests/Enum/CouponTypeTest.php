<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(CouponType::class)]
final class CouponTypeTest extends AbstractEnumTestCase
{
    public function testLabels(): void
    {
        self::assertSame('满减券', CouponType::FULL_REDUCTION->getLabel());
        self::assertSame('满赠券', CouponType::FULL_GIFT->getLabel());
        self::assertSame('兑换券', CouponType::REDEEM->getLabel());
        self::assertSame('买赠券', CouponType::BUY_GIFT->getLabel());
    }

    public function testToArray(): void
    {
        // Verify toArray() is called on each case
        self::assertSame(['value' => 'full_reduction', 'label' => '满减券'], CouponType::FULL_REDUCTION->toArray());
        self::assertSame(['value' => 'full_gift', 'label' => '满赠券'], CouponType::FULL_GIFT->toArray());
        self::assertSame(['value' => 'redeem', 'label' => '兑换券'], CouponType::REDEEM->toArray());
        self::assertSame(['value' => 'buy_gift', 'label' => '买赠券'], CouponType::BUY_GIFT->toArray());
    }
}
