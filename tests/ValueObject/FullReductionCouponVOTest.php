<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\ValueObject\CouponBenefitVO;
use Tourze\CouponCoreBundle\ValueObject\CouponConditionVO;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;
use Tourze\CouponCoreBundle\ValueObject\FullReductionCouponVO;

/**
 * @internal
 */
#[CoversClass(FullReductionCouponVO::class)]
final class FullReductionCouponVOTest extends TestCase
{
    public function testAccessors(): void
    {
        $vo = new FullReductionCouponVO(
            code: 'CODE',
            type: CouponType::FULL_REDUCTION,
            name: '测试',
            validFrom: null,
            validTo: null,
            scope: new CouponScopeVO(CouponScopeType::ALL),
            condition: new CouponConditionVO(thresholdAmount: '100.00'),
            benefit: new CouponBenefitVO(discountAmount: '20.00'),
            metadata: []
        );

        self::assertSame('20.00', $vo->getDiscountAmount());
    }
}
