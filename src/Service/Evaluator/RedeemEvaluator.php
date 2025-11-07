<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator;

use Tourze\CouponCoreBundle\Exception\CouponEvaluationException;
use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;
use Tourze\CouponCoreBundle\ValueObject\RedeemCouponVO;

/**
 * @internal
 */
class RedeemEvaluator implements CouponEvaluationStrategyInterface
{
    public function supports(CouponVO $coupon): bool
    {
        return $coupon instanceof RedeemCouponVO;
    }

    /**
     * @throws CouponEvaluationException
     */
    public function evaluate(CouponVO $coupon, CouponEvaluationContext $context): CouponApplicationResult
    {
        \assert($coupon instanceof RedeemCouponVO);

        $redeemItems = $coupon->getRedeemItems();
        if ([] === $redeemItems) {
            throw new CouponEvaluationException('兑换券未配置可兑换商品');
        }

        // 检查必需SPU条件
        if (!$context->hasRequiredSpus($coupon->getCondition()->getRequiredSpuIds())) {
            throw new CouponEvaluationException('未满足必需商品条件');
        }

        $total = '0.00';
        foreach ($redeemItems as $item) {
            $total = bcadd($total, $item->getSubtotal(), 2);
        }

        // 兑换商品不需要最低金额限制
        //        if (bccomp($total, '0.00', 2) <= 0) {
        //            throw new CouponEvaluationException('兑换商品金额无效');
        //        }

        return new CouponApplicationResult(
            $coupon->getCode(),
            $total,
            [],
            [],
            $redeemItems,
            true,
            [],
            [
                'redeem_value' => $total,
                'max_redeem_quantity' => $coupon->getMaxRedeemQuantity(),
                'coupon_type' => $coupon->getType()->value,
            ]
        );
    }
}
