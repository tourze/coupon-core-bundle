<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\ValueObject\CouponBenefitVO;
use Tourze\CouponCoreBundle\ValueObject\CouponConditionVO;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;
use Tourze\CouponCoreBundle\ValueObject\FullGiftCouponVO;
use Tourze\CouponCoreBundle\ValueObject\FullGiftTier;

/**
 * @internal
 */
#[CoversClass(FullGiftCouponVO::class)]
final class FullGiftCouponVOTest extends TestCase
{
    public function testGiftTiers(): void
    {
        $tier = FullGiftTier::fromArray([
            'threshold_amount' => '100.00',
            'gifts' => [['sku_id' => 'SKU', 'quantity' => 1]],
        ]);

        $condition = new CouponConditionVO(giftTiers: [$tier]);

        $vo = new FullGiftCouponVO(
            'CODE',
            CouponType::FULL_GIFT,
            'gift',
            null,
            null,
            new CouponScopeVO(CouponScopeType::ALL),
            $condition,
            new CouponBenefitVO(),
            []
        );

        self::assertSame($tier, $vo->getGiftTiers()[0]);
    }
}
