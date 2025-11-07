<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\ValueObject\CouponBenefitVO;
use Tourze\CouponCoreBundle\ValueObject\CouponConditionVO;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;
use Tourze\CouponCoreBundle\ValueObject\RedeemCouponVO;
use Tourze\CouponCoreBundle\ValueObject\RedeemItem;

/**
 * @internal
 */
#[CoversClass(RedeemCouponVO::class)]
final class RedeemCouponVOTest extends TestCase
{
    public function testRedeemItems(): void
    {
        $item = RedeemItem::fromArray([
            'sku_id' => 'SKU',
            'quantity' => 1,
            'unit_price' => '10.00',
        ]);

        $benefit = new CouponBenefitVO(redeemItems: [$item], markOrderPaid: true);
        $condition = new CouponConditionVO(maxRedeemQuantity: 2);

        $vo = new RedeemCouponVO(
            'CODE',
            CouponType::REDEEM,
            '兑换',
            null,
            null,
            new CouponScopeVO(CouponScopeType::ALL),
            $condition,
            $benefit,
            []
        );

        self::assertTrue($vo->getBenefit()->shouldMarkOrderPaid());
        self::assertCount(1, $vo->getRedeemItems());
        self::assertSame(2, $vo->getMaxRedeemQuantity());
    }
}
