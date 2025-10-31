<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeLockedEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(CodeLockedEvent::class)]
final class CodeLockedEventTest extends AbstractEventTestCase
{
    public function testEventCreation(): void
    {
        $event = new CodeLockedEvent();
        $this->assertInstanceOf(CodeLockedEvent::class, $event);
        $this->assertNull($event->getCode());
    }

    public function testCodeSetterAndGetter(): void
    {
        $event = new CodeLockedEvent();
        $code = new Code();

        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testCodeCanBeNull(): void
    {
        $event = new CodeLockedEvent();
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}
