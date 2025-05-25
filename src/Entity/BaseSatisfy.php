<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\Interface\SatisfyInterface;

/**
 * 使用条件基类
 */
#[ORM\Entity]
#[ORM\Table(name: 'coupon_satisfy_base')]
abstract class BaseSatisfy extends BaseCondition implements SatisfyInterface
{
    public function getScenario(): ConditionScenario
    {
        return ConditionScenario::SATISFY;
    }
} 