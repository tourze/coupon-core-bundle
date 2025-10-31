<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeUnlockEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(CodeUnlockEvent::class)]
final class CodeUnlockEventTest extends AbstractEventTestCase
{
    public function testEventCreation(): void
    {
        $event = new CodeUnlockEvent();
        $this->assertInstanceOf(CodeUnlockEvent::class, $event);
        $this->assertNull($event->getCode());
    }

    public function testCodeSetterAndGetter(): void
    {
        $event = new CodeUnlockEvent();
        $code = new Code();

        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testCodeCanBeNull(): void
    {
        $event = new CodeUnlockEvent();
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}
