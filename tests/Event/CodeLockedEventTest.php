<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeLockedEvent;

class CodeLockedEventTest extends TestCase
{
    public function testGetAndSetCode(): void
    {
        $event = new CodeLockedEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testSetCodeToNull(): void
    {
        $event = new CodeLockedEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}