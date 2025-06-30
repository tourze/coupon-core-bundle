<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Event\SendCodeEvent;

class SendCodeEventTest extends TestCase
{
    public function testGetAndSetUser(): void
    {
        $event = new SendCodeEvent();
        $user = $this->createMock(UserInterface::class);
        
        $event->setUser($user);
        $this->assertSame($user, $event->getUser());
    }

    public function testGetAndSetCoupon(): void
    {
        $event = new SendCodeEvent();
        $coupon = $this->createMock(Coupon::class);
        
        $event->setCoupon($coupon);
        $this->assertSame($coupon, $event->getCoupon());
    }

    public function testGetAndSetCode(): void
    {
        $event = new SendCodeEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testGetAndSetExtend(): void
    {
        $event = new SendCodeEvent();
        $extend = 'test_extension_data';
        
        $event->setExtend($extend);
        $this->assertSame($extend, $event->getExtend());
    }

    public function testSetExtendWithDifferentValues(): void
    {
        $event = new SendCodeEvent();
        
        // 测试设置普通字符串
        $event->setExtend('some_data');
        $this->assertSame('some_data', $event->getExtend());
        
        // 测试设置空字符串
        $event->setExtend('');
        $this->assertSame('', $event->getExtend());
    }

    public function testInitialExtendIsEmptyString(): void
    {
        $event = new SendCodeEvent();
        $this->assertSame('', $event->getExtend());
    }

    public function testSetCouponToNull(): void
    {
        $event = new SendCodeEvent();
        $coupon = $this->createMock(Coupon::class);
        
        $event->setCoupon($coupon);
        $event->setCoupon(null);
        $this->assertNull($event->getCoupon());
    }

    public function testSetCodeToNull(): void
    {
        $event = new SendCodeEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}