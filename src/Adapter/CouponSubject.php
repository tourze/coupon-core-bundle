<?php

namespace Tourze\CouponCoreBundle\Adapter;

use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 优惠券主体适配器
 */
class CouponSubject implements SubjectInterface
{
    public function __construct(
        private readonly Coupon $coupon
    ) {}

    public function getSubjectId(): string
    {
        return (string) $this->coupon->getId();
    }

    public function getSubjectType(): string
    {
        return 'coupon';
    }

    public function getSubjectData(): array
    {
        return [
            'id' => $this->coupon->getId(),
            'name' => $this->coupon->getName(),
            'sn' => $this->coupon->getSn(),
            'category_id' => $this->coupon->getCategory()?->getId(),
            'start_time' => $this->coupon->getStartTime()?->format('Y-m-d H:i:s'),
            'end_time' => $this->coupon->getEndTime()?->format('Y-m-d H:i:s'),
            'enabled' => $this->coupon->isValid(),
        ];
    }

    /**
     * 获取原始优惠券实体
     */
    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }
} 