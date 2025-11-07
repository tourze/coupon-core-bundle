<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator;

use Tourze\CouponCoreBundle\Exception\CouponEvaluationException;
use Tourze\CouponCoreBundle\Service\Evaluator\Helper\GiftCalculator;
use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;
use Tourze\CouponCoreBundle\ValueObject\FullGiftCouponVO;
use Tourze\CouponCoreBundle\ValueObject\FullGiftTier;

/**
 * @internal
 */
class FullGiftEvaluator implements CouponEvaluationStrategyInterface
{
    public function __construct(private readonly GiftCalculator $giftCalculator)
    {
    }

    public function supports(CouponVO $coupon): bool
    {
        return $coupon instanceof FullGiftCouponVO;
    }

    /**
     * @throws CouponEvaluationException
     */
    public function evaluate(CouponVO $coupon, CouponEvaluationContext $context): CouponApplicationResult
    {
        \assert($coupon instanceof FullGiftCouponVO);

        $eligibleItems = $context->filterItemsByScope($coupon->getScope());
        $total = $context->calculateItemsTotal($eligibleItems);
        $condition = $coupon->getCondition();

        // 对于无门槛优惠券，选择最低档位；否则按金额匹配档位
        if ($condition->isNoThreshold()) {
            $tiers = $condition->getGiftTiers();
            if ([] === $tiers) {
                throw new CouponEvaluationException('无门槛满赠券未配置档位');
            }
            // 选择最低门槛的档位（按降序排列后取最后一个）
            $sortedTiers = FullGiftTier::sortByThresholdDescending($tiers);
            $tier = false !== end($sortedTiers) ? end($sortedTiers) : null;
        } else {
            $tier = $condition->matchGiftTier($total);
        }

        if (null === $tier) {
            throw new CouponEvaluationException('未满足满赠门槛');
        }

        // 检查必需SPU条件
        if (!$context->hasRequiredSpus($condition->getRequiredSpuIds())) {
            throw new CouponEvaluationException('未满足必需商品条件');
        }

        $giftItems = $this->giftCalculator->normalizeGiftQuantities(
            $tier->getGifts(),
            $coupon->getCondition()->getMaxGifts()
        );

        if ([] === $giftItems) {
            throw new CouponEvaluationException('满赠档位未配置赠品');
        }

        return new CouponApplicationResult(
            $coupon->getCode(),
            '0.00',
            [],
            $giftItems,
            [],
            false,
            [],
            [
                'trigger_threshold' => $tier->getThresholdAmount(),
                'eligible_total' => $total,
                'coupon_type' => $coupon->getType()->value,
            ]
        );
    }
}
