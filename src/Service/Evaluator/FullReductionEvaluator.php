<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\CouponCoreBundle\Exception\CouponEvaluationException;
use Tourze\CouponCoreBundle\Service\Evaluator\Helper\DiscountAllocator;
use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponOrderItem;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;
use Tourze\CouponCoreBundle\ValueObject\FullReductionCouponVO;

/**
 * @internal
 */
#[AutoconfigureTag(name: 'coupon.evaluator.strategy')]
#[WithMonologChannel(channel: 'coupon_core')]
readonly class FullReductionEvaluator implements CouponEvaluationStrategyInterface
{
    public function __construct(
        private DiscountAllocator $allocator,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(CouponVO $coupon): bool
    {
        return $coupon instanceof FullReductionCouponVO;
    }

    /**
     * @throws CouponEvaluationException
     */
    public function evaluate(CouponVO $coupon, CouponEvaluationContext $context): CouponApplicationResult
    {
        \assert($coupon instanceof FullReductionCouponVO);
        $this->logger->debug('处理满减优惠券', [
            'coupon_code' => $coupon->getCode(),
            'coupon' => $coupon->toArray(),
            'context' => $context->getMetadata(),
        ]);

        $eligibleItems = $this->filterEligibleItems($context, $coupon);
        if ([] === $eligibleItems) {
            throw new CouponEvaluationException('无可参与满减的商品');
        }

        $total = $context->calculateItemsTotal($eligibleItems);
        $condition = $coupon->getCondition();
        // 检查门槛金额（无门槛优惠券跳过此检查）
        if (!$condition->isNoThreshold()) {
            $threshold = $condition->getThresholdAmount();
            if (null !== $threshold && bccomp($total, $threshold, 2) < 0) {
                throw new CouponEvaluationException('未满足满减门槛');
            }
        }

        // 检查必需SPU条件
        if (!$context->hasRequiredSpus($condition->getRequiredSpuIds())) {
            throw new CouponEvaluationException('未满足必需商品条件');
        }

        $discount = $coupon->getDiscountAmount();
        if (null === $discount || bccomp($discount, '0.00', 2) <= 0) {
            throw new CouponEvaluationException('优惠券未配置减免金额');
        }

        if (bccomp($discount, $total, 2) > 0) {
            $discount = $total;
        }

        $allocations = $this->allocator->allocate(
            $coupon->getAllocationRule(),
            $eligibleItems,
            $discount,
            $coupon->getCondition()->getPrioritySkuIds()
        );

        return new CouponApplicationResult(
            $coupon->getCode(),
            sprintf('%.2f', (float) $discount),
            $allocations,
            [],
            [],
            false,
            [],
            [
                'allocation_rule' => $coupon->getAllocationRule()->value,
                'eligible_total' => $total,
                'coupon_type' => $coupon->getType()->value,
            ]
        );
    }

    /**
     * @return list<CouponOrderItem>
     */
    private function filterEligibleItems(CouponEvaluationContext $context, FullReductionCouponVO $coupon): array
    {
        return $context->filterItemsByScope($coupon->getScope());
    }
}
