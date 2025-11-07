<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Enum\AllocationRule;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(AllocationRule::class)]
final class AllocationRuleTest extends AbstractEnumTestCase
{
    public function testLabels(): void
    {
        self::assertSame('按金额占比分摊', AllocationRule::PROPORTIONAL->getLabel());
        self::assertSame('平均分摊', AllocationRule::AVERAGE->getLabel());
        self::assertSame('优先商品分摊', AllocationRule::PRIORITY->getLabel());
    }
}
