<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Interface\RequirementInterface;

/**
 * 领取条件基类
 */
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_base')]
abstract class BaseRequirement extends BaseCondition implements RequirementInterface
{
    public function getScenario(): ConditionScenario
    {
        return ConditionScenario::REQUIREMENT;
    }
} 