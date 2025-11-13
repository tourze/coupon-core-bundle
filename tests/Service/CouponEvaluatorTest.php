<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\Service\CouponEvaluator;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\CouponCoreBundle\Service\Evaluator\BuyGiftEvaluator;
use Tourze\CouponCoreBundle\Service\Evaluator\FullGiftEvaluator;
use Tourze\CouponCoreBundle\Service\Evaluator\FullReductionEvaluator;
use Tourze\CouponCoreBundle\Service\Evaluator\Helper\DiscountAllocator;
use Tourze\CouponCoreBundle\Service\Evaluator\Helper\GiftCalculator;
use Tourze\CouponCoreBundle\Service\Evaluator\RedeemEvaluator;
use Tourze\CouponCoreBundle\ValueObject\CouponBenefitVO;
use Tourze\CouponCoreBundle\ValueObject\CouponConditionVO;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponOrderItem;
use Tourze\CouponCoreBundle\ValueObject\CouponScopeVO;
use Tourze\CouponCoreBundle\Enum\CouponScopeType;
use Psr\Log\NullLogger;
use Tourze\CouponCoreBundle\ValueObject\FullReductionCouponVO;

/**
 * @internal
 */
#[CoversClass(CouponEvaluator::class)]
#[RunTestsInSeparateProcesses]
final class CouponEvaluatorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的设置逻辑已由父类 AbstractIntegrationTestCase 处理
        // 这里不需要额外的设置，因为所有必需的依赖都已通过继承获得
    }

    public function testEvaluateFullReduction(): void
    {
        $coupon = new FullReductionCouponVO(
            'CODE',
            CouponType::FULL_REDUCTION,
            '满减',
            null,
            null,
            new CouponScopeVO(CouponScopeType::ALL),
            new CouponConditionVO(thresholdAmount: '50.00'),
            new CouponBenefitVO(discountAmount: '10.00')
        );

        $context = new CouponEvaluationContext(
            null,
            [new CouponOrderItem('SKU1', 1, '60.00', true, null, null, null, null, '60.00')]
        );

        $evaluator = self::getService(CouponEvaluator::class);
        $result = $evaluator->evaluate($coupon, $context);

        self::assertSame('10.00', $result->getDiscountAmount());
        self::assertTrue($result->hasDiscount());
    }
}
