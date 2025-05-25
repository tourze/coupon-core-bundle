<?php

namespace Tourze\CouponCoreBundle\Interface;

use Tourze\CouponCoreBundle\ValueObject\OrderContext;

/**
 * 使用条件处理器接口
 */
interface SatisfyHandlerInterface extends ConditionHandlerInterface
{
    /**
     * 验证订单是否满足使用条件
     */
    public function checkSatisfy(SatisfyInterface $satisfy, OrderContext $orderContext): bool;
}
