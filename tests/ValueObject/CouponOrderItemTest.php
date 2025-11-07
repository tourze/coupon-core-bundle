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
        // 使用命名参数确保subtotal正确传入第9个参数位置
        $item = new CouponOrderItem(
            skuId: 'SKU1',
            quantity: 2,
            unitPrice: '10.00',
            subtotal: '20.00'
        );
        $discounted = $item->withAllocatedDiscount('5.00');

        self::assertSame('15.00', $discounted->getSubtotal());
        self::assertSame('7.50', $discounted->getUnitPrice());
    }
}
