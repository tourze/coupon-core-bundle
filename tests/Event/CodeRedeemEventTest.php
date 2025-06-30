<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeRedeemEvent;

class CodeRedeemEventTest extends TestCase
{
    public function testGetAndSetCode(): void
    {
        $event = new CodeRedeemEvent();
        $code = $this->createMock(Code::class);
        
        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testGetAndSetExtra(): void
    {
        $event = new CodeRedeemEvent();
        $extra = new stdClass();
        $extra->data = 'test_data';
        
        $event->setExtra($extra);
        $this->assertSame($extra, $event->getExtra());
    }

    public function testSetExtraToNull(): void
    {
        $event = new CodeRedeemEvent();
        $extra = new stdClass();
        
        $event->setExtra($extra);
        $event->setExtra(null);
        $this->assertNull($event->getExtra());
    }

    public function testInitialExtraIsNull(): void
    {
        $event = new CodeRedeemEvent();
        $this->assertNull($event->getExtra());
    }
}