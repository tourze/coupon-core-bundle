<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Enum\CodeStatus;

class CodeStatusTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_get_status_unused(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        $this->code->setExpireTime(new DateTime('+1 day'));
        
        $this->assertEquals(CodeStatus::UNUSED, $this->code->getStatus());
    }
    
    public function test_get_status_used(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        $this->code->setUseTime(new DateTime());
        
        $this->assertEquals(CodeStatus::USED, $this->code->getStatus());
    }
    
    public function test_get_status_expired(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        $this->code->setExpireTime(new DateTime('-1 day'));
        
        $this->assertEquals(CodeStatus::EXPIRED, $this->code->getStatus());
    }
    
    public function test_get_status_invalid_due_to_coupon(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(false);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        
        $this->assertEquals(CodeStatus::INVALID, $this->code->getStatus());
    }
    
    public function test_get_status_invalid_due_to_code(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(false);
        
        $this->assertEquals(CodeStatus::INVALID, $this->code->getStatus());
    }
    
    public function test_status_priority_used_over_expired(): void
    {
        // 测试状态优先级：已使用 > 已过期
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        $this->code->setUseTime(new DateTime()); // 已使用
        $this->code->setExpireTime(new DateTime('-1 day')); // 已过期
        
        // 即使过期了，但因为已使用，状态应该是 USED
        $this->assertEquals(CodeStatus::USED, $this->code->getStatus());
    }
    
    public function test_status_priority_invalid_over_expired(): void
    {
        // 测试状态优先级：无效 > 已过期
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(false); // 优惠券无效
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        $this->code->setUseTime(null); // 未使用
        $this->code->setExpireTime(new DateTime('-1 day')); // 已过期
        
        // 优惠券无效的优先级高于过期
        $this->assertEquals(CodeStatus::INVALID, $this->code->getStatus());
    }
}
