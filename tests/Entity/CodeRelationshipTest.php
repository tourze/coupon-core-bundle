<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\ReadStatus;

class CodeRelationshipTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_coupon_relationship(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->assertSame($coupon, $this->code->getCoupon());
    }
    
    public function test_coupon_relationship_with_null(): void
    {
        $this->code->setCoupon(null);
        $this->assertNull($this->code->getCoupon());
    }
    
    public function test_channel_relationship(): void
    {
        $channel = $this->createMock(Channel::class);
        
        $this->code->setChannel($channel);
        $this->assertSame($channel, $this->code->getChannel());
    }
    
    public function test_channel_relationship_with_null(): void
    {
        $this->code->setChannel(null);
        $this->assertNull($this->code->getChannel());
    }
    
    public function test_owner_relationship(): void
    {
        $owner = $this->createMock(UserInterface::class);
        
        $this->code->setOwner($owner);
        $this->assertSame($owner, $this->code->getOwner());
    }
    
    public function test_owner_relationship_with_null(): void
    {
        $this->code->setOwner(null);
        $this->assertNull($this->code->getOwner());
    }
    
    public function test_read_status_relationship(): void
    {
        $readStatus = $this->createMock(ReadStatus::class);
        $readStatus->method('getCode')->willReturn($this->code);
        
        $this->code->setReadStatus($readStatus);
        $this->assertSame($readStatus, $this->code->getReadStatus());
    }
}
