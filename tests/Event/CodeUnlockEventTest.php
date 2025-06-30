<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeUnlockEvent;

class CodeUnlockEventTest extends TestCase
{
    public function testGetAndSetCode(): void
    {
        $event = new CodeUnlockEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testSetCodeToNull(): void
    {
        $event = new CodeUnlockEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }

    public function testInitialCodeIsNull(): void
    {
        $event = new CodeUnlockEvent();
        $this->assertNull($event->getCode());
    }
}