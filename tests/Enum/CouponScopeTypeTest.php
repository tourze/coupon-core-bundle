<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(CouponScopeType::class)]
final class CouponScopeTypeTest extends AbstractEnumTestCase
{
    public function testLabels(): void
    {
        self::assertSame('全场商品', CouponScopeType::ALL->getLabel());
        self::assertSame('指定SKU', CouponScopeType::SKU->getLabel());
        self::assertSame('指定SPU', CouponScopeType::SPU->getLabel());
        self::assertSame('指定品类', CouponScopeType::CATEGORY->getLabel());
    }

    public function testToArray(): void
    {
        // Verify toArray() is called on each case
        self::assertSame(['value' => 'all', 'label' => '全场商品'], CouponScopeType::ALL->toArray());
        self::assertSame(['value' => 'sku', 'label' => '指定SKU'], CouponScopeType::SKU->toArray());
        self::assertSame(['value' => 'spu', 'label' => '指定SPU'], CouponScopeType::SPU->toArray());
        self::assertSame(['value' => 'category', 'label' => '指定品类'], CouponScopeType::CATEGORY->toArray());
    }
}
