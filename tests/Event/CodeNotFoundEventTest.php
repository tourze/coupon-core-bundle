<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeNotFoundEvent;

class CodeNotFoundEventTest extends TestCase
{
    public function testGetAndSetSn(): void
    {
        $event = new CodeNotFoundEvent();
        $sn = 'TEST_SN_12345';
        
        $event->setSn($sn);
        $this->assertSame($sn, $event->getSn());
    }

    public function testGetAndSetUser(): void
    {
        $event = new CodeNotFoundEvent();
        $user = $this->createMock(UserInterface::class);
        
        $event->setUser($user);
        $this->assertSame($user, $event->getUser());
    }

    public function testGetAndSetCode(): void
    {
        $event = new CodeNotFoundEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testSetCodeToNull(): void
    {
        $event = new CodeNotFoundEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}