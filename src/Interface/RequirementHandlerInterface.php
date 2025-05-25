<?php

namespace Tourze\CouponCoreBundle\Interface;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 领取条件处理器接口
 */
interface RequirementHandlerInterface extends ConditionHandlerInterface
{
    /**
     * 验证用户是否满足领取条件
     */
    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool;
}
