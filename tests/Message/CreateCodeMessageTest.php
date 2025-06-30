<?php

namespace Tourze\CouponCoreBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Message\CreateCodeMessage;

class CreateCodeMessageTest extends TestCase
{
    public function testGetSetCouponId(): void
    {
        $message = new CreateCodeMessage();
        $message->setCouponId(123);
        
        $this->assertSame(123, $message->getCouponId());
    }
    
    public function testGetSetQuantity(): void
    {
        $message = new CreateCodeMessage();
        $message->setQuantity(50);
        
        $this->assertSame(50, $message->getQuantity());
    }
}