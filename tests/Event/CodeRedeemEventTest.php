<?php

namespace Tourze\CouponCoreBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Event\CodeRedeemEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(CodeRedeemEvent::class)]
final class CodeRedeemEventTest extends AbstractEventTestCase
{
    public function testEventCreation(): void
    {
        $event = new CodeRedeemEvent();
        $this->assertInstanceOf(CodeRedeemEvent::class, $event);
        $this->assertNull($event->getCode());
    }

    public function testCodeSetterAndGetter(): void
    {
        $event = new CodeRedeemEvent();
        $code = new Code();

        $event->setCode($code);
        $this->assertSame($code, $event->getCode());
    }

    public function testCodeCanBeNull(): void
    {
        $event = new CodeRedeemEvent();
        $event->setCode(null);
        $this->assertNull($event->getCode());
    }
}
