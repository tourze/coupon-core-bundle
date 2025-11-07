<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\ValueObject\CouponConditionVO;
use Tourze\CouponCoreBundle\ValueObject\FullGiftTier;

/**
 * @internal
 */
#[CoversClass(CouponConditionVO::class)]
final class CouponConditionVOTest extends TestCase
{
    public function testMatchGiftTier(): void
    {
        $tier = FullGiftTier::fromArray([
            'threshold_amount' => '100.00',
            'gifts' => [
                ['sku_id' => 'SKU', 'quantity' => 1],
            ],
        ]);

        $condition = new CouponConditionVO(
            thresholdAmount: '100.00',
            giftTiers: [$tier]
        );

        self::assertSame($tier, $condition->matchGiftTier('150.00'));
        self::assertNull($condition->matchGiftTier('80.00'));
    }
}
