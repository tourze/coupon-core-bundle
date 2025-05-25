<?php

namespace Tourze\CouponCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\CouponCoreBundle\Traits\CouponAware;

class DetectCouponEvent extends Event
{
    use CouponAware;

    private string $couponId;

    public function getCouponId(): string
    {
        return $this->couponId;
    }

    public function setCouponId(string $couponId): void
    {
        $this->couponId = $couponId;
    }
}
