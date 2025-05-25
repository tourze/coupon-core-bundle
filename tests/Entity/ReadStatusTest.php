<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\ReadStatus;

class ReadStatusTest extends TestCase
{
    private ReadStatus $readStatus;

    protected function setUp(): void
    {
        $this->readStatus = new ReadStatus();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(ReadStatus::class, $this->readStatus);
        $this->assertNull($this->readStatus->getId());
    }

    public function test_getter_and_setter_methods(): void
    {
        $createdBy = 'test_user';
        $updatedBy = 'test_updater';

        $this->readStatus->setCreatedBy($createdBy);
        $this->readStatus->setUpdatedBy($updatedBy);

        $this->assertEquals($createdBy, $this->readStatus->getCreatedBy());
        $this->assertEquals($updatedBy, $this->readStatus->getUpdatedBy());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->readStatus->setCreateTime($createTime);
        $this->readStatus->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->readStatus->getCreateTime());
        $this->assertEquals($updateTime, $this->readStatus->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->readStatus->setCreateTime(null);
        $this->readStatus->setUpdateTime(null);

        $this->assertNull($this->readStatus->getCreateTime());
        $this->assertNull($this->readStatus->getUpdateTime());
    }

    public function test_code_relationship(): void
    {
        $code = new Code();
        $code->setSn('TEST-CODE-001');

        $this->readStatus->setCode($code);

        $this->assertSame($code, $this->readStatus->getCode());
        $this->assertEquals('TEST-CODE-001', $this->readStatus->getCode()->getSn());
    }

    public function test_retrieve_api_array(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->readStatus->setCreateTime($createTime);
        $this->readStatus->setUpdateTime($updateTime);
        $this->readStatus->setCreatedBy('user123');

        $result = $this->readStatus->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);
        $this->assertArrayHasKey('createdBy', $result);

        $this->assertEquals('2023-01-01 10:00:00', $result['createTime']);
        $this->assertEquals('2023-01-02 11:00:00', $result['updateTime']);
        $this->assertEquals('user123', $result['createdBy']);
    }

    public function test_retrieve_api_array_with_null_values(): void
    {
        $this->readStatus->setCreateTime(null);
        $this->readStatus->setUpdateTime(null);
        $this->readStatus->setCreatedBy(null);

        $result = $this->readStatus->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
        $this->assertNull($result['createdBy']);
    }

    public function test_fluent_interface(): void
    {
        $code = new Code();
        $code->setSn('FLUENT-TEST');

        $result = $this->readStatus
            ->setCode($code)
            ->setCreatedBy('user')
            ->setUpdatedBy('updater');

        $this->assertSame($this->readStatus, $result);
        $this->assertEquals('user', $this->readStatus->getCreatedBy());
        $this->assertEquals('updater', $this->readStatus->getUpdatedBy());
        $this->assertSame($code, $this->readStatus->getCode());
    }

    public function test_created_by_with_null(): void
    {
        $this->readStatus->setCreatedBy(null);
        $this->assertNull($this->readStatus->getCreatedBy());
    }

    public function test_updated_by_with_null(): void
    {
        $this->readStatus->setUpdatedBy(null);
        $this->assertNull($this->readStatus->getUpdatedBy());
    }

    public function test_created_by_with_empty_string(): void
    {
        $this->readStatus->setCreatedBy('');
        $this->assertEquals('', $this->readStatus->getCreatedBy());
    }

    public function test_updated_by_with_empty_string(): void
    {
        $this->readStatus->setUpdatedBy('');
        $this->assertEquals('', $this->readStatus->getUpdatedBy());
    }

    public function test_special_characters_in_user_fields(): void
    {
        $specialUser = 'user@example.com_测试用户';
        $specialUpdater = 'admin#123$%^&*()';

        $this->readStatus->setCreatedBy($specialUser);
        $this->readStatus->setUpdatedBy($specialUpdater);

        $this->assertEquals($specialUser, $this->readStatus->getCreatedBy());
        $this->assertEquals($specialUpdater, $this->readStatus->getUpdatedBy());
    }

    public function test_code_relationship_bidirectional(): void
    {
        $code = new Code();
        $code->setSn('BIDIRECTIONAL-TEST');
        
        $this->readStatus->setCode($code);
        $code->setReadStatus($this->readStatus);

        $this->assertSame($code, $this->readStatus->getCode());
        $this->assertSame($this->readStatus, $code->getReadStatus());
    }

    public function test_complete_read_status_scenario(): void
    {
        $code = new Code();
        $code->setSn('COMPLETE-READ-TEST');
        
        $createTime = new \DateTime('2023-01-01 08:00:00');
        $updateTime = new \DateTime('2023-01-01 08:05:00');

        $this->readStatus->setCode($code);
        $this->readStatus->setCreatedBy('reader_user');
        $this->readStatus->setUpdatedBy('system');
        $this->readStatus->setCreateTime($createTime);
        $this->readStatus->setUpdateTime($updateTime);

        // 验证所有属性都正确设置
        $this->assertSame($code, $this->readStatus->getCode());
        $this->assertEquals('reader_user', $this->readStatus->getCreatedBy());
        $this->assertEquals('system', $this->readStatus->getUpdatedBy());
        $this->assertEquals($createTime, $this->readStatus->getCreateTime());
        $this->assertEquals($updateTime, $this->readStatus->getUpdateTime());

        // 验证业务逻辑：更新时间应该晚于或等于创建时间
        $this->assertGreaterThanOrEqual(
            $createTime->getTimestamp(),
            $updateTime->getTimestamp(),
            '更新时间应该晚于或等于创建时间'
        );
    }

    public function test_read_tracking_scenario(): void
    {
        // 模拟用户查看券码的跟踪场景
        $code = new Code();
        $code->setSn('READ-TRACK-001');
        
        $readTime = new \DateTime();
        $userId = 'user_12345';

        $this->readStatus->setCode($code);
        $this->readStatus->setCreatedBy($userId);
        $this->readStatus->setCreateTime($readTime);

        $this->assertEquals($userId, $this->readStatus->getCreatedBy());
        $this->assertEquals($readTime, $this->readStatus->getCreateTime());
        $this->assertSame($code, $this->readStatus->getCode());
    }

    public function test_system_generated_read_status(): void
    {
        // 模拟系统自动生成的阅读状态
        $code = new Code();
        $code->setSn('SYSTEM-GENERATED');
        
        $this->readStatus->setCode($code);
        $this->readStatus->setCreatedBy('system');
        $this->readStatus->setUpdatedBy('system');
        $this->readStatus->setCreateTime(new \DateTime());
        $this->readStatus->setUpdateTime(new \DateTime());

        $this->assertEquals('system', $this->readStatus->getCreatedBy());
        $this->assertEquals('system', $this->readStatus->getUpdatedBy());
        $this->assertNotNull($this->readStatus->getCreateTime());
        $this->assertNotNull($this->readStatus->getUpdateTime());
    }

    public function test_long_user_identifier(): void
    {
        $longUserId = str_repeat('a', 100); // 假设数据库支持较长的用户标识符

        $this->readStatus->setCreatedBy($longUserId);
        $this->readStatus->setUpdatedBy($longUserId);

        $this->assertEquals($longUserId, $this->readStatus->getCreatedBy());
        $this->assertEquals($longUserId, $this->readStatus->getUpdatedBy());
    }
} 