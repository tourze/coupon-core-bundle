<?php

namespace Tourze\CouponCoreBundle\Interface;

use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;

/**
 * 条件接口
 */
interface ConditionInterface
{
    public function getId(): ?int;

    public function getCoupon(): ?Coupon;

    public function getType(): string;

    public function getLabel(): string;

    public function isEnabled(): bool;

    public function getScenario(): ConditionScenario;

    public function toArray(): array;
}
