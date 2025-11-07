<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\ValueObject\RedeemItem;

/**
 * @internal
 */
#[CoversClass(RedeemItem::class)]
final class RedeemItemTest extends TestCase
{
    public function testSubtotal(): void
    {
        $item = RedeemItem::fromArray([
            'sku_id' => 'SKU1',
            'quantity' => 3,
            'unit_price' => '5.00',
        ]);

        self::assertSame('15.00', $item->getSubtotal());
    }
}
