<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\ReadStatus;
use Tourze\CouponCoreBundle\Enum\CodeStatus;

class CodeTest extends TestCase
{
    private Code $code;
    private Coupon&MockObject $coupon;
    private Channel&MockObject $channel;
    private UserInterface&MockObject $owner;
    private ReadStatus&MockObject $readStatus;
    
    protected function setUp(): void
    {
        $this->code = new Code();
        
        // 初始化常用的mock对象
        $this->coupon = $this->createMock(Coupon::class);
        $this->coupon->method('isValid')->willReturn(true);
        $this->coupon->method('retrieveApiArray')->willReturn(['id' => 1, 'name' => 'Test Coupon']);
        $this->coupon->method('retrieveAdminArray')->willReturn(['id' => 1, 'name' => 'Admin Test Coupon']);
        
        $this->channel = $this->createMock(Channel::class);
        $this->channel->method('retrieveApiArray')->willReturn(['id' => 2, 'name' => 'Test Channel']);
        
        $this->owner = $this->createMock(UserInterface::class);
        $this->owner->method('getUserIdentifier')->willReturn('test_user');
        
        $this->readStatus = $this->createMock(ReadStatus::class);
        $this->readStatus->method('retrieveApiArray')->willReturn(['id' => 3, 'status' => 'read']);
        $this->readStatus->method('getCode')->willReturn($this->code);
    }
    
    public function testBasicFunctionality(): void
    {
        // 创建渠道实体用于测试
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        
        // 测试基础属性
        $this->code->setSn('TEST_CODE_12345');
        $this->code->setGatherChannel($gatherChannel);
        $this->code->setUseChannel($useChannel);
        $this->code->setConsumeCount(3);
        $this->code->setValid(true);
        $this->code->setLocked(false);
        $this->code->setNeedActive(true);
        $this->code->setActive(false);
        $this->code->setRemark('测试备注信息');
        
        $this->assertEquals('TEST_CODE_12345', $this->code->getSn());
        $this->assertSame($gatherChannel, $this->code->getGatherChannel());
        $this->assertSame($useChannel, $this->code->getUseChannel());
        $this->assertEquals(3, $this->code->getConsumeCount());
        $this->assertTrue($this->code->isValid());
        $this->assertFalse($this->code->isLocked());
        $this->assertTrue($this->code->isNeedActive());
        $this->assertFalse($this->code->isActive());
        $this->assertEquals('测试备注信息', $this->code->getRemark());
        
        // 测试时间相关属性
        $now = new DateTime();
        $this->code->setGatherTime($now);
        $this->code->setExpireTime($now);
        $this->code->setUseTime($now);
        $this->code->setActiveTime($now);
        $this->code->setCreateTime($now);
        $this->code->setUpdateTime($now);
        
        $this->assertSame($now, $this->code->getGatherTime());
        $this->assertSame($now, $this->code->getExpireTime());
        $this->assertSame($now, $this->code->getUseTime());
        $this->assertSame($now, $this->code->getActiveTime());
        $this->assertSame($now, $this->code->getCreateTime());
        $this->assertSame($now, $this->code->getUpdateTime());
    }
    
    public function testRelationships(): void
    {
        $this->code->setCoupon($this->coupon);
        $this->code->setChannel($this->channel);
        $this->code->setOwner($this->owner);
        $this->code->setReadStatus($this->readStatus);
        
        $this->assertSame($this->coupon, $this->code->getCoupon());
        $this->assertSame($this->channel, $this->code->getChannel());
        $this->assertSame($this->owner, $this->code->getOwner());
        $this->assertSame($this->readStatus, $this->code->getReadStatus());
        
        // 测试关系可以设置为null
        $this->code->setCoupon(null);
        $this->code->setChannel(null);
        $this->code->setOwner(null);
        
        $this->assertNull($this->code->getCoupon());
        $this->assertNull($this->code->getChannel());
        $this->assertNull($this->code->getOwner());
    }
    
    public function testCodeStatus(): void
    {
        $this->code->setCoupon($this->coupon);
        
        // 测试未使用状态
        $this->code->setValid(true);
        $this->code->setUseTime(null);
        $this->code->setExpireTime(new DateTime('+1 day'));
        $this->assertEquals(CodeStatus::UNUSED, $this->code->getStatus());
        
        // 测试已使用状态
        $this->code->setUseTime(new DateTime());
        $this->assertEquals(CodeStatus::USED, $this->code->getStatus());
        
        // 测试已过期状态
        $this->code->setUseTime(null);
        $this->code->setExpireTime(new DateTime('-1 day'));
        $this->assertEquals(CodeStatus::EXPIRED, $this->code->getStatus());
        
        // 测试无效状态
        $invalidCoupon = $this->createMock(Coupon::class);
        $invalidCoupon->method('isValid')->willReturn(false);
        $this->code->setCoupon($invalidCoupon);
        $this->assertEquals(CodeStatus::INVALID, $this->code->getStatus());
    }
    
    public function testCodeLifecycle(): void
    {
        // 创建渠道实体用于测试
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        
        // 测试完整的生命周期
        $this->code->setCoupon($this->coupon);
        $this->code->setSn('LIFECYCLE_TEST');
        $this->code->setValid(true);
        $this->code->setCreateTime(new DateTime());
        $this->code->setGatherTime(new DateTime());
        $this->code->setExpireTime(new DateTime('+30 days'));
        $this->code->setGatherChannel($gatherChannel);
        $this->code->setOwner($this->owner);
        $this->code->setNeedActive(true);
        $this->code->setActive(true);
        $this->code->setActiveTime(new DateTime());
        $this->code->setUseTime(new DateTime());
        $this->code->setUseChannel($useChannel);
        $this->code->setConsumeCount(1);
        
        $this->assertEquals(CodeStatus::USED, $this->code->getStatus());
        $this->assertTrue($this->code->isValid());
        $this->assertTrue($this->code->isActive());
        $this->assertEquals(1, $this->code->getConsumeCount());
    }
    
    public function testBusinessLogic(): void
    {
        $this->code->setSn('BIZ_TEST_CODE');
        
        // 测试二维码链接生成
        $qrcodeData = $this->code->getQrcodeLink();
        $this->assertIsArray($qrcodeData);
        $this->assertEquals('BIZ_TEST_CODE', $qrcodeData['code']);
        $this->assertEquals('BIZ_TEST_CODE', $qrcodeData['sn']);
        $this->assertIsInt($qrcodeData['t']);
        
        // 测试有效期文本
        $this->code->setGatherTime(new DateTime('2024-01-01'));
        $this->code->setExpireTime(new DateTime('2024-01-31'));
        $this->assertEquals('有效期:2024.01.01至2024.01.31', $this->code->getValidPeriodText());
    }
    
    public function testArrayOutput(): void
    {
        // 创建渠道实体用于测试
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        $gatherChannel->method('retrieveApiArray')->willReturn(['id' => 5, 'name' => 'API Test Channel']);
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        $useChannel->method('retrieveApiArray')->willReturn(['id' => 6, 'name' => 'API Use Channel']);
        
        $this->code->setCoupon($this->coupon);
        $this->code->setChannel($this->channel);
        $this->code->setOwner($this->owner);
        $this->code->setReadStatus($this->readStatus);
        $this->code->setSn('API_TEST_CODE');
        $this->code->setGatherChannel($gatherChannel);
        $this->code->setUseChannel($useChannel);
        $this->code->setValid(true);
        $this->code->setLocked(false);
        $this->code->setGatherTime(new DateTime('2024-01-01 10:00:00'));
        $this->code->setExpireTime(new DateTime('2024-12-31 23:59:59'));
        $this->code->setUseTime(new DateTime('2024-06-15 14:30:00'));
        
        $apiArray = $this->code->retrieveApiArray();
        $this->assertIsArray($apiArray);
        $this->assertEquals('API_TEST_CODE', $apiArray['sn']);
        $this->assertEquals(['id' => 5, 'name' => 'API Test Channel'], $apiArray['gather_channel']);
        $this->assertEquals(['id' => 6, 'name' => 'API Use Channel'], $apiArray['use_channel']);
        
        $adminArray = $this->code->retrieveAdminArray();
        $this->assertIsArray($adminArray);
        $this->assertEquals('API_TEST_CODE', $adminArray['sn']);
        $this->assertTrue($adminArray['valid']);
    }
    
    public function testEdgeCases(): void
    {
        // 测试空值
        $this->code->setSn('');
        $this->code->setGatherChannel(null);
        $this->code->setUseChannel(null);
        $this->code->setRemark('');
        
        $this->assertEquals('', $this->code->getSn());
        $this->assertNull($this->code->getGatherChannel());
        $this->assertNull($this->code->getUseChannel());
        $this->assertEquals('', $this->code->getRemark());
        
        // 测试特殊字符
        $specialString = '特殊字符!@#$%^&*()_+-=[]{}|;:,.<>?/~`';
        $this->code->setSn($specialString);
        $this->assertEquals($specialString, $this->code->getSn());
        
        // 测试极值
        $this->code->setConsumeCount(PHP_INT_MAX);
        $this->assertEquals(PHP_INT_MAX, $this->code->getConsumeCount());
        
        // 测试toString
        $this->assertEquals('#0 ' . $specialString, (string)$this->code);
    }
}
