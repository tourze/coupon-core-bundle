<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\SendCodeEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(SendCodeEvent::class)]
final class SendCodeEventTest extends AbstractEventTestCase
{
    public function testEventCreation(): void
    {
        $event = new SendCodeEvent();
        $this->assertInstanceOf(SendCodeEvent::class, $event);
        $this->assertNull($event->getCode());
    }

    public function testCodeSetterAndGetter(): void
    {
        $event = new SendCodeEvent();
        $code = new Code();

        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testCodeCanBeNull(): void
    {
        $event = new SendCodeEvent();
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}
