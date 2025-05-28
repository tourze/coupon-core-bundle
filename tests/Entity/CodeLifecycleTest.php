<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CodeLifecycleTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_code_lifecycle_generation(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setSn('LIFECYCLE_TEST_CODE');
        $this->code->setValid(true);
        $this->code->setCreateTime(new DateTime());
        
        $this->assertTrue($this->code->isValid());
        $this->assertNotNull($this->code->getCreateTime());
        $this->assertEquals('LIFECYCLE_TEST_CODE', $this->code->getSn());
    }
    
    public function test_code_lifecycle_gathering(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        
        $this->code->setCoupon($coupon);
        $this->code->setSn('LIFECYCLE_TEST_CODE');
        $this->code->setValid(true);
        $this->code->setGatherChannel($gatherChannel);
        $this->code->setGatherTime(new DateTime());
        $this->code->setExpireTime(new DateTime('+30 days'));
        
        $this->assertTrue($this->code->isValid());
        $this->assertNotNull($this->code->getGatherTime());
        $this->assertNotNull($this->code->getExpireTime());
        $this->assertSame($gatherChannel, $this->code->getGatherChannel());
    }
    
    public function test_code_lifecycle_activation(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setSn('LIFECYCLE_TEST_CODE');
        $this->code->setValid(true);
        $this->code->setNeedActive(true);
        $this->code->setActive(true);
        $this->code->setActiveTime(new DateTime());
        
        $this->assertTrue($this->code->isValid());
        $this->assertTrue($this->code->isNeedActive());
        $this->assertTrue($this->code->isActive());
        $this->assertNotNull($this->code->getActiveTime());
    }
    
    public function test_code_lifecycle_usage(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        
        $this->code->setCoupon($coupon);
        $this->code->setSn('LIFECYCLE_TEST_CODE');
        $this->code->setValid(true);
        $this->code->setUseChannel($useChannel);
        $this->code->setUseTime(new DateTime());
        $this->code->setConsumeCount(1);
        
        $this->assertTrue($this->code->isValid());
        $this->assertNotNull($this->code->getUseTime());
        $this->assertSame($useChannel, $this->code->getUseChannel());
        $this->assertEquals(1, $this->code->getConsumeCount());
    }
    
    public function test_code_lifecycle_expiration(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setSn('LIFECYCLE_TEST_CODE');
        $this->code->setValid(true);
        $this->code->setExpireTime(new DateTime('-1 day'));
        
        $this->assertTrue($this->code->isValid());
        $this->assertNotNull($this->code->getExpireTime());
        $this->assertTrue($this->code->getExpireTime() < new DateTime());
    }
    
    public function test_multiple_consume_scenario(): void
    {
        // 测试多次消费场景（为未来功能预留）
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isValid')->willReturn(true);
        
        $this->code->setCoupon($coupon);
        $this->code->setValid(true);
        $this->code->setConsumeCount(3);
        
        $this->assertEquals(3, $this->code->getConsumeCount());
        
        // 增加消费次数
        $this->code->setConsumeCount($this->code->getConsumeCount() + 1);
        $this->assertEquals(4, $this->code->getConsumeCount());
    }
}
