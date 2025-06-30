<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Event\DetectCouponEvent;

class DetectCouponEventTest extends TestCase
{
    public function testGetAndSetCouponId(): void
    {
        $event = new DetectCouponEvent();
        $couponId = 'COUPON_12345';
        
        $event->setCouponId($couponId);
        $this->assertSame($couponId, $event->getCouponId());
    }

    public function testGetAndSetCoupon(): void
    {
        $event = new DetectCouponEvent();
        $coupon = $this->createMock(Coupon::class);
        
        $event->setCoupon($coupon);
        $this->assertSame($coupon, $event->getCoupon());
    }

    public function testSetCouponToNull(): void
    {
        $event = new DetectCouponEvent();
        $coupon = $this->createMock(Coupon::class);
        
        $event->setCoupon($coupon);
        $event->setCoupon(null);
        $this->assertNull($event->getCoupon());
    }

    public function testInitialCouponIsNull(): void
    {
        $event = new DetectCouponEvent();
        $this->assertNull($event->getCoupon());
    }
}