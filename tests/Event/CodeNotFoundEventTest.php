<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeNotFoundEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(CodeNotFoundEvent::class)]
final class CodeNotFoundEventTest extends AbstractEventTestCase
{
    public function testEventCreation(): void
    {
        $event = new CodeNotFoundEvent();
        $this->assertInstanceOf(CodeNotFoundEvent::class, $event);
        $this->assertNull($event->getCode());
    }

    public function testCodeSetterAndGetter(): void
    {
        $event = new CodeNotFoundEvent();
        $code = new Code();

        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testCodeCanBeNull(): void
    {
        $event = new CodeNotFoundEvent();
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}
