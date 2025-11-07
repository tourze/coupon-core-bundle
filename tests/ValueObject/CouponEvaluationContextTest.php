<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponOrderItem;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;

/**
 * @internal
 */
#[CoversClass(CouponEvaluationContext::class)]
final class CouponEvaluationContextTest extends TestCase
{
    public function testFilterItemsByScope(): void
    {
        $items = [
            new CouponOrderItem('SKU1', 1, '10.00', true, null, null, '10.00'),
            new CouponOrderItem('SKU2', 1, '5.00', false, null, null, '5.00'),
        ];

        $context = new CouponEvaluationContext(null, $items);
        $scope = new CouponScopeVO(CouponScopeType::ALL);

        $eligible = $context->filterItemsByScope($scope);

        self::assertCount(1, $eligible);
        self::assertSame('SKU1', $eligible[0]->getSkuId());
    }
}
