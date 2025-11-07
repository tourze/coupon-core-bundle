<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator;

use Tourze\CouponCoreBundle\Exception\CouponEvaluationException;
use Tourze\CouponCoreBundle\Service\Evaluator\Helper\GiftCalculator;
use Tourze\CouponCoreBundle\ValueObject\BuyGiftCouponVO;
use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;
use Tourze\CouponCoreBundle\ValueObject\GiftItem;

/**
 * @internal
 */
class BuyGiftEvaluator implements CouponEvaluationStrategyInterface
{
    public function __construct(private readonly GiftCalculator $giftCalculator)
    {
    }

    public function supports(CouponVO $coupon): bool
    {
        return $coupon instanceof BuyGiftCouponVO;
    }

    /**
     * @throws CouponEvaluationException
     */
    public function evaluate(CouponVO $coupon, CouponEvaluationContext $context): CouponApplicationResult
    {
        \assert($coupon instanceof BuyGiftCouponVO);

        $eligibleItems = $context->filterItemsByScope($coupon->getScope());
        $requirements = $coupon->getBuyRequirements();
        if ([] === $requirements) {
            throw new CouponEvaluationException('买赠券未配置主商品要求');
        }

        $purchasedSets = $this->giftCalculator->calculateBuyGiftSets($eligibleItems, $requirements);
        if ($purchasedSets <= 0) {
            throw new CouponEvaluationException('未满足买赠数量要求');
        }

        // 检查必需SPU条件
        if (!$context->hasRequiredSpus($coupon->getCondition()->getRequiredSpuIds())) {
            throw new CouponEvaluationException('未满足必需商品条件');
        }

        $giftItems = $coupon->getGiftItems();
        if ([] === $giftItems) {
            throw new CouponEvaluationException('买赠券未配置赠品');
        }

        $maxGifts = $coupon->getMaxGifts();
        $resultGifts = [];
        foreach ($giftItems as $gift) {
            $quantity = $gift->getQuantity() * $purchasedSets;
            if ($maxGifts > 0) {
                $quantity = min($quantity, $maxGifts);
            }
            if ($quantity <= 0) {
                continue;
            }
            $resultGifts[] = new GiftItem($gift->getSkuId(), $quantity, $gift->getGtin(), $gift->getName());
        }

        if ([] === $resultGifts) {
            throw new CouponEvaluationException('买赠券计算后无可用赠品');
        }

        return new CouponApplicationResult(
            $coupon->getCode(),
            '0.00',
            [],
            $resultGifts,
            [],
            false,
            [],
            [
                'purchased_sets' => $purchasedSets,
                'coupon_type' => $coupon->getType()->value,
            ]
        );
    }
}
