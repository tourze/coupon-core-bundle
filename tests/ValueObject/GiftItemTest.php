<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\ValueObject\GiftItem;

/**
 * @internal
 */
#[CoversClass(GiftItem::class)]
final class GiftItemTest extends TestCase
{
    public function testFromArray(): void
    {
        $item = GiftItem::fromArray([
            'gtin' => 'SKU1',
            'quantity' => 2,
            'name' => '赠品',
        ]);

        self::assertSame('SKU1', $item->getGtin());
        self::assertSame(2, $item->getQuantity());
        self::assertSame('赠品', $item->getName());
    }
}
