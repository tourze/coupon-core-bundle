<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Enum\CodeStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(CodeStatus::class)]
final class CodeStatusTest extends AbstractEnumTestCase
{
    public function testEnumExists(): void
    {
        $this->assertTrue(enum_exists(CodeStatus::class));
    }

    public function testToArray(): void
    {
        $expected = [
            'value' => 'unused',
            'label' => '未使用',
        ];

        $this->assertEquals($expected, CodeStatus::UNUSED->toArray());
    }
}
