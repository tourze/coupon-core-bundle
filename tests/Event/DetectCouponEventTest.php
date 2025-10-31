<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Event\DetectCouponEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(DetectCouponEvent::class)]
final class DetectCouponEventTest extends AbstractEventTestCase
{
    public function testEventCreation(): void
    {
        $event = new DetectCouponEvent();
        $this->assertInstanceOf(DetectCouponEvent::class, $event);
        $this->assertNull($event->getCoupon());
    }

    public function testCouponSetterAndGetter(): void
    {
        $event = new DetectCouponEvent();
        $coupon = new Coupon();

        $event->setCoupon($coupon);
        $this->assertSame($coupon, $event->getCoupon());
    }

    public function testCouponCanBeNull(): void
    {
        $event = new DetectCouponEvent();
        $event->setCoupon(null);
        $this->assertNull($event->getCoupon());
    }

    public function testCouponIdSetterAndGetter(): void
    {
        $event = new DetectCouponEvent();
        $couponId = 'test-coupon-123';

        $event->setCouponId($couponId);
        $this->assertEquals($couponId, $event->getCouponId());
    }
}
