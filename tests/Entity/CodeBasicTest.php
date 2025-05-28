<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;

class CodeBasicTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Code::class, $this->code);
        $this->assertEquals(0, $this->code->getId());
        $this->assertFalse($this->code->isValid());
        $this->assertFalse($this->code->isLocked());
    }
    
    public function test_basic_getter_and_setter_methods(): void
    {
        // 测试券码编号
        $this->code->setSn('TEST_CODE_12345');
        $this->assertEquals('TEST_CODE_12345', $this->code->getSn());
        
        // 测试领取渠道 - 现在需要使用 Channel 实体
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        $this->code->setGatherChannel($gatherChannel);
        $this->assertSame($gatherChannel, $this->code->getGatherChannel());
        
        // 测试使用渠道 - 现在需要使用 Channel 实体
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        $this->code->setUseChannel($useChannel);
        $this->assertSame($useChannel, $this->code->getUseChannel());
        
        // 测试核销次数
        $this->code->setConsumeCount(3);
        $this->assertEquals(3, $this->code->getConsumeCount());
        
        // 测试备注
        $this->code->setRemark('测试备注信息');
        $this->assertEquals('测试备注信息', $this->code->getRemark());
    }
    
    public function test_boolean_properties(): void
    {
        // 测试有效性
        $this->code->setValid(true);
        $this->assertTrue($this->code->isValid());
        
        $this->code->setValid(false);
        $this->assertFalse($this->code->isValid());
        
        // 测试锁定状态
        $this->code->setLocked(true);
        $this->assertTrue($this->code->isLocked());
        
        $this->code->setLocked(false);
        $this->assertFalse($this->code->isLocked());
        
        // 测试是否需要激活
        $this->code->setNeedActive(true);
        $this->assertTrue($this->code->isNeedActive());
        
        $this->code->setNeedActive(false);
        $this->assertFalse($this->code->isNeedActive());
        
        // 测试是否已激活
        $this->code->setActive(true);
        $this->assertTrue($this->code->isActive());
        
        $this->code->setActive(false);
        $this->assertFalse($this->code->isActive());
    }
    
    public function test_datetime_properties(): void
    {
        $gatherTime = new DateTime('2024-01-01 10:00:00');
        $expireTime = new DateTime('2024-12-31 23:59:59');
        $useTime = new DateTime('2024-06-15 14:30:00');
        $activeTime = new DateTime('2024-01-02 09:00:00');
        $createTime = new DateTime('2024-01-01 08:00:00');
        $updateTime = new DateTime('2024-01-01 10:30:00');
        
        $this->code->setGatherTime($gatherTime);
        $this->code->setExpireTime($expireTime);
        $this->code->setUseTime($useTime);
        $this->code->setActiveTime($activeTime);
        $this->code->setCreateTime($createTime);
        $this->code->setUpdateTime($updateTime);
        
        $this->assertSame($gatherTime, $this->code->getGatherTime());
        $this->assertSame($expireTime, $this->code->getExpireTime());
        $this->assertSame($useTime, $this->code->getUseTime());
        $this->assertSame($activeTime, $this->code->getActiveTime());
        $this->assertSame($createTime, $this->code->getCreateTime());
        $this->assertSame($updateTime, $this->code->getUpdateTime());
    }
    
    public function test_datetime_properties_with_null_values(): void
    {
        $this->code->setGatherTime(null);
        $this->code->setExpireTime(null);
        $this->code->setUseTime(null);
        $this->code->setActiveTime(null);
        
        $this->assertNull($this->code->getGatherTime());
        $this->assertNull($this->code->getExpireTime());
        $this->assertNull($this->code->getUseTime());
        $this->assertNull($this->code->getActiveTime());
    }
    
    public function test_audit_fields(): void
    {
        $this->code->setCreatedBy('admin');
        $this->code->setUpdatedBy('system');
        
        $this->assertEquals('admin', $this->code->getCreatedBy());
        $this->assertEquals('system', $this->code->getUpdatedBy());
    }
    
    public function test_to_string_method(): void
    {
        $this->code->setSn('STRING_TEST_CODE');
        
        // 由于 getId() 返回 0，我们需要模拟一个有效的 ID
        $reflection = new \ReflectionClass($this->code);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->code, 123);
        
        $stringRepresentation = (string) $this->code;
        
        $this->assertEquals('#123 STRING_TEST_CODE', $stringRepresentation);
    }
    
    public function test_to_string_method_with_default_id(): void
    {
        $this->code->setSn('DEFAULT_ID_CODE');
        
        $stringRepresentation = (string) $this->code;
        
        $this->assertEquals('#0 DEFAULT_ID_CODE', $stringRepresentation);
    }
    
    public function test_to_string_method_with_empty_sn(): void
    {
        $this->code->setSn('');
        
        // 设置一个有效的 ID
        $reflection = new \ReflectionClass($this->code);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->code, 456);
        
        $stringRepresentation = (string) $this->code;
        
        $this->assertEquals('#456 ', $stringRepresentation);
    }
}
