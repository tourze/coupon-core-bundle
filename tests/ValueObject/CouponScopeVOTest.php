<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;

/**
 * @internal
 */
#[CoversClass(CouponScopeVO::class)]
final class CouponScopeVOTest extends TestCase
{
    public function testSkuEligibility(): void
    {
        $scope = new CouponScopeVO(CouponScopeType::SKU, ['SKU1']);

        self::assertTrue($scope->isSkuEligible('SKU1'));
        self::assertFalse($scope->isSkuEligible('SKU2'));
    }
}
