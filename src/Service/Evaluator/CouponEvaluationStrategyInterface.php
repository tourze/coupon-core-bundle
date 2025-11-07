<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator;

use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;

interface CouponEvaluationStrategyInterface
{
    public function supports(CouponVO $coupon): bool;

    public function evaluate(CouponVO $coupon, CouponEvaluationContext $context): CouponApplicationResult;
}
