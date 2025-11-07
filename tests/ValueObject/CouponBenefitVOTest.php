<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\AllocationRule;
use Tourze\CouponCoreBundle\ValueObject\CouponBenefitVO;

/**
 * @internal
 */
#[CoversClass(CouponBenefitVO::class)]
final class CouponBenefitVOTest extends TestCase
{
    public function testFromArray(): void
    {
        $benefit = CouponBenefitVO::fromArray([
            'discount_amount' => '20.00',
            'allocation' => 'average',
        ]);

        self::assertSame('20.00', $benefit->getDiscountAmount());
        self::assertSame(AllocationRule::AVERAGE, $benefit->getAllocationRule());
    }
}
