<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\ValueObject\CouponOrderItem;

/**
 * @internal
 */
#[CoversClass(CouponOrderItem::class)]
final class CouponOrderItemTest extends TestCase
{
    public function testAllocatedDiscount(): void
    {
        $item = new CouponOrderItem('SKU1', 2, '10.00', true, null, null, '20.00');
        $discounted = $item->withAllocatedDiscount('5.00');

        self::assertSame('15.00', $discounted->getSubtotal());
        self::assertSame('7.50', $discounted->getUnitPrice());
    }
}
