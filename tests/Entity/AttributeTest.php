<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Attribute;
use Tourze\CouponCoreBundle\Entity\Coupon;

class AttributeTest extends TestCase
{
    private Attribute $attribute;

    protected function setUp(): void
    {
        $this->attribute = new Attribute();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Attribute::class, $this->attribute);
        $this->assertNull($this->attribute->getId());
        $this->assertNull($this->attribute->getName());
        $this->assertNull($this->attribute->getValue());
    }

    public function test_getter_and_setter_methods(): void
    {
        $name = 'test_attribute';
        $value = 'test_value';
        $remark = 'test_remark';
        $createdBy = 'test_user';
        $updatedBy = 'test_updater';
        $createdFromIp = '192.168.1.1';
        $updatedFromIp = '192.168.1.2';

        $this->attribute->setName($name);
        $this->attribute->setValue($value);
        $this->attribute->setRemark($remark);
        $this->attribute->setCreatedBy($createdBy);
        $this->attribute->setUpdatedBy($updatedBy);
        $this->attribute->setCreatedFromIp($createdFromIp);
        $this->attribute->setUpdatedFromIp($updatedFromIp);

        $this->assertEquals($name, $this->attribute->getName());
        $this->assertEquals($value, $this->attribute->getValue());
        $this->assertEquals($remark, $this->attribute->getRemark());
        $this->assertEquals($createdBy, $this->attribute->getCreatedBy());
        $this->assertEquals($updatedBy, $this->attribute->getUpdatedBy());
        $this->assertEquals($createdFromIp, $this->attribute->getCreatedFromIp());
        $this->assertEquals($updatedFromIp, $this->attribute->getUpdatedFromIp());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->attribute->setCreateTime($createTime);
        $this->attribute->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->attribute->getCreateTime());
        $this->assertEquals($updateTime, $this->attribute->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->attribute->setCreateTime(null);
        $this->attribute->setUpdateTime(null);

        $this->assertNull($this->attribute->getCreateTime());
        $this->assertNull($this->attribute->getUpdateTime());
    }

    public function test_coupon_relationship(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $this->attribute->setCoupon($coupon);

        $this->assertSame($coupon, $this->attribute->getCoupon());
    }

    public function test_coupon_relationship_with_null(): void
    {
        $this->attribute->setCoupon(null);

        $this->assertNull($this->attribute->getCoupon());
    }

    public function test_retrieve_api_array(): void
    {
        $this->attribute->setName('test_name');
        $this->attribute->setValue('test_value');
        
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');
        $this->attribute->setCreateTime($createTime);
        $this->attribute->setUpdateTime($updateTime);

        $result = $this->attribute->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);
        
        $this->assertEquals('test_name', $result['name']);
        $this->assertEquals('test_value', $result['value']);
        $this->assertEquals('2023-01-01 10:00:00', $result['createTime']);
        $this->assertEquals('2023-01-02 11:00:00', $result['updateTime']);
    }

    public function test_retrieve_api_array_with_null_dates(): void
    {
        $this->attribute->setName('test_name');
        $this->attribute->setValue('test_value');

        $result = $this->attribute->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
    }

    public function test_retrieve_admin_array(): void
    {
        $this->attribute->setName('admin_test');
        $this->attribute->setValue('admin_value');

        $result = $this->attribute->retrieveAdminArray();
        $apiResult = $this->attribute->retrieveApiArray();

        $this->assertEquals($apiResult, $result);
    }

    public function test_name_with_empty_string(): void
    {
        $this->attribute->setName('');
        $this->assertEquals('', $this->attribute->getName());
    }

    public function test_value_with_empty_string(): void
    {
        $this->attribute->setValue('');
        $this->assertEquals('', $this->attribute->getValue());
    }

    public function test_value_with_long_text(): void
    {
        $longText = str_repeat('A', 1000);
        $this->attribute->setValue($longText);
        $this->assertEquals($longText, $this->attribute->getValue());
    }

    public function test_remark_with_null(): void
    {
        $this->attribute->setRemark(null);
        $this->assertNull($this->attribute->getRemark());
    }

    public function test_fluent_interface(): void
    {
        $result = $this->attribute
            ->setName('fluent_test')
            ->setValue('fluent_value')
            ->setRemark('fluent_remark')
            ->setCreatedBy('fluent_user')
            ->setUpdatedBy('fluent_updater')
            ->setCreatedFromIp('127.0.0.1')
            ->setUpdatedFromIp('127.0.0.2');

        $this->assertSame($this->attribute, $result);
        $this->assertEquals('fluent_test', $this->attribute->getName());
        $this->assertEquals('fluent_value', $this->attribute->getValue());
        $this->assertEquals('fluent_remark', $this->attribute->getRemark());
    }

    public function test_ip_address_validation(): void
    {
        $validIpv4 = '192.168.1.1';
        $validIpv6 = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $this->attribute->setCreatedFromIp($validIpv4);
        $this->attribute->setUpdatedFromIp($validIpv6);

        $this->assertEquals($validIpv4, $this->attribute->getCreatedFromIp());
        $this->assertEquals($validIpv6, $this->attribute->getUpdatedFromIp());
    }

    public function test_name_length_boundary(): void
    {
        // 测试长度为120的名称（数据库字段限制）
        $name120 = str_repeat('A', 120);
        $this->attribute->setName($name120);
        $this->assertEquals($name120, $this->attribute->getName());
    }

    public function test_special_characters_in_name(): void
    {
        $specialName = 'test-name_123.attribute';
        $this->attribute->setName($specialName);
        $this->assertEquals($specialName, $this->attribute->getName());
    }

    public function test_special_characters_in_value(): void
    {
        $specialValue = 'value with spaces, symbols: @#$%^&*()';
        $this->attribute->setValue($specialValue);
        $this->assertEquals($specialValue, $this->attribute->getValue());
    }
} 