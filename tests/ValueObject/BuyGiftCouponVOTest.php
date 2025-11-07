<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\ValueObject\BuyGiftCouponVO;
use Tourze\CouponCoreBundle\ValueObject\CouponBenefitVO;
use Tourze\CouponCoreBundle\ValueObject\CouponConditionVO;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;
use Tourze\CouponCoreBundle\ValueObject\GiftItem;

/**
 * @internal
 */
#[CoversClass(BuyGiftCouponVO::class)]
final class BuyGiftCouponVOTest extends TestCase
{
    public function testGiftQuantity(): void
    {
        $condition = new CouponConditionVO(buyRequirements: [['sku_id' => 'SKU1', 'quantity' => 2]], maxGifts: 5);
        $benefit = new CouponBenefitVO(giftItems: [new GiftItem('SKU2', 1)]);

        $vo = new BuyGiftCouponVO(
            'CODE',
            CouponType::BUY_GIFT,
            '买赠',
            null,
            null,
            new CouponScopeVO(),
            $condition,
            $benefit,
            []
        );

        self::assertSame(5, $vo->getMaxGifts());
        self::assertSame('SKU2', $vo->getGiftItems()[0]->getSkuId());
    }
}
