<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Enum\DiscountType;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(DiscountType::class)]
final class DiscountTypeTest extends AbstractEnumTestCase
{
    public function testEnumExists(): void
    {
        $this->assertTrue(enum_exists(DiscountType::class));
    }

    public function testToArray(): void
    {
        $expected = [
            'value' => 'freight',
            'label' => '抵扣运费',
        ];

        $this->assertEquals($expected, DiscountType::FREIGHT->toArray());
    }
}
