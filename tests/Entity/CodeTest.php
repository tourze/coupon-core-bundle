<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CodeTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function testGetterAndSetterMethods(): void
    {
        // ID是自动生成的，我们不应该手动设置
        $coupon = new Coupon();
        $this->code->setCoupon($coupon);
        $this->assertSame($coupon, $this->code->getCoupon());

        $this->code->setSn('CODE12345');
        $this->assertEquals('CODE12345', $this->code->getSn());

        // 设置和获取有效性
        $this->code->setValid(true);
        $this->assertTrue($this->code->isValid());
        
        $this->code->setValid(false);
        $this->assertFalse($this->code->isValid());
        
        // 设置和获取所有者
        $owner = $this->createMock(UserInterface::class);
        $this->code->setOwner($owner);
        $this->assertSame($owner, $this->code->getOwner());
        
        // 设置和获取领取时间
        $gatherTime = new DateTime();
        $this->code->setGatherTime($gatherTime);
        $this->assertSame($gatherTime, $this->code->getGatherTime());
        
        // 设置和获取过期时间
        $expireTime = new DateTime('+30 days');
        $this->code->setExpireTime($expireTime);
        $this->assertSame($expireTime, $this->code->getExpireTime());
        
        // 设置和获取使用时间
        $useTime = new DateTime();
        $this->code->setUseTime($useTime);
        $this->assertSame($useTime, $this->code->getUseTime());
    }
    
    /**
     * 测试是否过期的逻辑
     */
    public function testExpirationStatus(): void
    {
        // 设置过期时间为过去
        $pastDate = new DateTime('-1 day');
        $this->code->setExpireTime($pastDate);
        $this->code->setUseTime(null); // 未使用
        
        // 此时应该已经过期（有过期时间且已过期）
        $this->assertTrue($this->code->getExpireTime() < new DateTime());
        
        // 设置过期时间为未来
        $futureDate = new DateTime('+1 day');
        $this->code->setExpireTime($futureDate);
        
        // 此时不应该过期
        $this->assertTrue($this->code->getExpireTime() > new DateTime());
    }
    
    /**
     * 测试是否已使用的逻辑
     */
    public function testUsageStatus(): void
    {
        // 初始状态应该是未使用
        $this->assertNull($this->code->getUseTime());
        
        // 设置使用时间
        $useTime = new DateTime();
        $this->code->setUseTime($useTime);
        
        // 此时应该已经使用
        $this->assertNotNull($this->code->getUseTime());
    }

    /**
     * 测试API数组输出
     */
    public function testRetrieveApiArray(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $this->code->setCoupon($coupon);
        $this->code->setSn('CODE12345');
        
        // 设置必要的日期，确保API数组包含这些字段
        $gatherTime = new DateTime();
        $this->code->setGatherTime($gatherTime);
        
        $expireTime = new DateTime('+30 days');
        $this->code->setExpireTime($expireTime);
        
        $useTime = null;
        $this->code->setUseTime($useTime);
        
        $apiArray = $this->code->retrieveApiArray();
        
        // 基本验证
        $this->assertIsArray($apiArray);
        $this->assertEquals('CODE12345', $apiArray['sn'] ?? null);
        $this->assertArrayHasKey('expireTime', $apiArray);
    }
    
    /**
     * 测试toString方法
     */
    public function testToStringMethod(): void
    {
        $this->code->setSn('CODE12345');
        
        // 使用反射设置ID
        $reflection = new \ReflectionClass($this->code);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->code, 0);
        
        $this->assertEquals('#0 CODE12345', (string)$this->code);
    }
}
