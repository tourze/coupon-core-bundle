<?php

namespace Tourze\CouponCoreBundle\Tests\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Message\CreateCodeMessage;

/**
 * @internal
 */
#[CoversClass(CreateCodeMessage::class)]
final class CreateCodeMessageTest extends TestCase
{
    public function testMessageCreation(): void
    {
        $message = new CreateCodeMessage();
        $this->assertInstanceOf(CreateCodeMessage::class, $message);
    }

    public function testCouponIdSetterAndGetter(): void
    {
        $message = new CreateCodeMessage();
        $couponId = 123;

        $message->setCouponId($couponId);
        $this->assertEquals($couponId, $message->getCouponId());
    }

    public function testQuantitySetterAndGetter(): void
    {
        $message = new CreateCodeMessage();
        $quantity = 50;

        $message->setQuantity($quantity);
        $this->assertEquals($quantity, $message->getQuantity());
    }
}
