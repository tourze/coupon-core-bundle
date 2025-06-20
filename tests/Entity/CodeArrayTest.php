<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\ReadStatus;
use Tourze\CouponCoreBundle\Enum\CodeStatus;

class CodeArrayTest extends TestCase
{
    private Code $code;
    
    protected function setUp(): void
    {
        $this->code = new Code();
    }
    
    public function test_retrieve_api_array(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 1, 'name' => 'Test Coupon']);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $channel */
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 2, 'name' => 'Test Channel']);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        $gatherChannel->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 3, 'name' => 'Mobile App']);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        $useChannel->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 4, 'name' => 'WeChat Mini']);
        
        /** @var UserInterface&\PHPUnit\Framework\MockObject\MockObject $owner */
        $owner = $this->createMock(UserInterface::class);
        $owner->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('test_user');
        
        /** @var ReadStatus&\PHPUnit\Framework\MockObject\MockObject $readStatus */
        $readStatus = $this->createMock(ReadStatus::class);
        $readStatus->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 3, 'status' => 'read']);
        
        $reflection = new \ReflectionClass($this->code);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->code, 123);
        
        $this->code->setSn('TEST_CODE_12345');
        $this->code->setGatherChannel($gatherChannel);
        $this->code->setUseChannel($useChannel);
        $this->code->setConsumeCount(3);
        $this->code->setValid(true);
        $this->code->setLocked(false);
        $this->code->setNeedActive(true);
        $this->code->setActive(false);
        $this->code->setGatherTime(new DateTimeImmutable('2024-01-01 10:00:00'));
        $this->code->setExpireTime(new DateTimeImmutable('2024-12-31 23:59:59'));
        $this->code->setUseTime(new DateTimeImmutable('2024-06-15 14:30:00'));
        $this->code->setActiveTime(new DateTimeImmutable('2024-01-02 09:00:00'));
        $this->code->setRemark('测试备注信息');
        $this->code->setCoupon($coupon);
        $this->code->setChannel($channel);
        $this->code->setOwner($owner);
        $this->code->setReadStatus($readStatus);
        
        $expected = [
            'id' => 123,
            'sn' => 'TEST_CODE_12345',
            'gather_channel' => ['id' => 3, 'name' => 'Mobile App'],
            'use_channel' => ['id' => 4, 'name' => 'WeChat Mini'],
            'consume_count' => 3,
            'valid' => true,
            'locked' => false,
            'need_active' => true,
            'active' => false,
            'gather_time' => '2024-01-01 10:00:00',
            'expire_time' => '2024-12-31 23:59:59',
            'use_time' => '2024-06-15 14:30:00',
            'active_time' => '2024-01-02 09:00:00',
            'remark' => '测试备注信息',
            'status' => CodeStatus::USED->value,
            'coupon' => ['id' => 1, 'name' => 'Test Coupon'],
            'channel' => ['id' => 2, 'name' => 'Test Channel'],
            'owner' => 'test_user',
            'read_status' => ['id' => 3, 'status' => 'read'],
        ];
        
        $this->assertEquals($expected, $this->code->retrieveApiArray());
    }
    
    public function test_retrieve_admin_array(): void
    {
        /** @var Coupon&\PHPUnit\Framework\MockObject\MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);
        $coupon->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 1, 'name' => 'Test Coupon']);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $channel */
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 2, 'name' => 'Test Channel']);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $gatherChannel */
        $gatherChannel = $this->createMock(Channel::class);
        $gatherChannel->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 3, 'name' => 'Mobile App']);
        
        /** @var Channel&\PHPUnit\Framework\MockObject\MockObject $useChannel */
        $useChannel = $this->createMock(Channel::class);
        $useChannel->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 4, 'name' => 'WeChat Mini']);
        
        /** @var UserInterface&\PHPUnit\Framework\MockObject\MockObject $owner */
        $owner = $this->createMock(UserInterface::class);
        $owner->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('test_user');
        
        /** @var ReadStatus&\PHPUnit\Framework\MockObject\MockObject $readStatus */
        $readStatus = $this->createMock(ReadStatus::class);
        $readStatus->expects($this->once())
            ->method('retrieveApiArray')
            ->willReturn(['id' => 3, 'status' => 'read']);
        
        // 使用反射设置 id
        $reflection = new \ReflectionClass($this->code);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->code, 123);
        
        $this->code->setSn('TEST_CODE_12345');
        $this->code->setGatherChannel($gatherChannel);
        $this->code->setUseChannel($useChannel);
        $this->code->setConsumeCount(3);
        $this->code->setValid(true);
        $this->code->setLocked(false);
        $this->code->setNeedActive(true);
        $this->code->setActive(false);
        $this->code->setGatherTime(new DateTimeImmutable('2024-01-01 10:00:00'));
        $this->code->setExpireTime(new DateTimeImmutable('2024-12-31 23:59:59'));
        $this->code->setUseTime(new DateTimeImmutable('2024-06-15 14:30:00'));
        $this->code->setActiveTime(new DateTimeImmutable('2024-01-02 09:00:00'));
        $this->code->setRemark('测试备注信息');
        $this->code->setCreatedBy('admin');
        $this->code->setUpdatedBy('system');
        $this->code->setCreateTime(new DateTimeImmutable('2024-01-01 08:00:00'));
        $this->code->setUpdateTime(new DateTimeImmutable('2024-01-01 10:30:00'));
        $this->code->setCoupon($coupon);
        $this->code->setChannel($channel);
        $this->code->setOwner($owner);
        $this->code->setReadStatus($readStatus);
        
        $expected = [
            'id' => 123,
            'sn' => 'TEST_CODE_12345',
            'gather_channel' => ['id' => 3, 'name' => 'Mobile App'],
            'use_channel' => ['id' => 4, 'name' => 'WeChat Mini'],
            'consume_count' => 3,
            'valid' => true,
            'locked' => false,
            'need_active' => true,
            'active' => false,
            'gather_time' => '2024-01-01 10:00:00',
            'expire_time' => '2024-12-31 23:59:59',
            'use_time' => '2024-06-15 14:30:00',
            'active_time' => '2024-01-02 09:00:00',
            'remark' => '测试备注信息',
            'status' => CodeStatus::USED->value,
            'created_by' => 'admin',
            'updated_by' => 'system',
            'create_time' => '2024-01-01 08:00:00',
            'update_time' => '2024-01-01 10:30:00',
            'coupon' => ['id' => 1, 'name' => 'Test Coupon'],
            'channel' => ['id' => 2, 'name' => 'Test Channel'],
            'owner' => 'test_user',
            'read_status' => ['id' => 3, 'status' => 'read'],
        ];
        
        $this->assertEquals($expected, $this->code->retrieveAdminArray());
    }
}
