<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\Discount;
use Tourze\CouponCoreBundle\Enum\DiscountType;

class DiscountTest extends TestCase
{
    private Discount $discount;

    protected function setUp(): void
    {
        $this->discount = new Discount();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Discount::class, $this->discount);
        $this->assertNull($this->discount->getId());
        $this->assertNull($this->discount->getCoupon());
        $this->assertNull($this->discount->getType());
        $this->assertNull($this->discount->getValue());
    }

    public function test_getter_and_setter_methods(): void
    {
        $type = DiscountType::ORDER;
        $value = '100';
        $remark = 'Test discount remark';
        $createdBy = 'admin';
        $updatedBy = 'moderator';
        $createdFromIp = '192.168.1.1';
        $updatedFromIp = '192.168.1.2';

        $this->discount->setType($type);
        $this->discount->setValue($value);
        $this->discount->setRemark($remark);
        $this->discount->setCreatedBy($createdBy);
        $this->discount->setUpdatedBy($updatedBy);
        $this->discount->setCreatedFromIp($createdFromIp);
        $this->discount->setUpdatedFromIp($updatedFromIp);

        $this->assertEquals($type, $this->discount->getType());
        $this->assertEquals($value, $this->discount->getValue());
        $this->assertEquals($remark, $this->discount->getRemark());
        $this->assertEquals($createdBy, $this->discount->getCreatedBy());
        $this->assertEquals($updatedBy, $this->discount->getUpdatedBy());
        $this->assertEquals($createdFromIp, $this->discount->getCreatedFromIp());
        $this->assertEquals($updatedFromIp, $this->discount->getUpdatedFromIp());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');

        $this->discount->setCreateTime($createTime);
        $this->discount->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->discount->getCreateTime());
        $this->assertEquals($updateTime, $this->discount->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->discount->setCreateTime(null);
        $this->discount->setUpdateTime(null);

        $this->assertNull($this->discount->getCreateTime());
        $this->assertNull($this->discount->getUpdateTime());
    }

    public function test_coupon_relationship(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $this->discount->setCoupon($coupon);

        $this->assertSame($coupon, $this->discount->getCoupon());
    }

    public function test_coupon_relationship_with_null(): void
    {
        $this->discount->setCoupon(null);

        $this->assertNull($this->discount->getCoupon());
    }

    public function test_discount_type_order(): void
    {
        $this->discount->setType(DiscountType::ORDER);
        $this->assertEquals(DiscountType::ORDER, $this->discount->getType());
    }

    public function test_discount_type_freight(): void
    {
        $this->discount->setType(DiscountType::FREIGHT);
        $this->assertEquals(DiscountType::FREIGHT, $this->discount->getType());
    }

    public function test_value_with_numeric_string(): void
    {
        $numericValues = ['0', '1', '10', '100', '1000', '99999'];

        foreach ($numericValues as $value) {
            $this->discount->setValue($value);
            $this->assertEquals($value, $this->discount->getValue());
        }
    }

    public function test_value_with_decimal_string(): void
    {
        $decimalValues = ['0.01', '1.50', '99.99', '100.00', '999.99'];

        foreach ($decimalValues as $value) {
            $this->discount->setValue($value);
            $this->assertEquals($value, $this->discount->getValue());
        }
    }

    public function test_value_with_empty_string(): void
    {
        $this->discount->setValue('');
        $this->assertEquals('', $this->discount->getValue());
    }

    public function test_value_with_json_string(): void
    {
        $jsonValue = '{"amount": 100, "currency": "CNY"}';
        $this->discount->setValue($jsonValue);
        $this->assertEquals($jsonValue, $this->discount->getValue());
    }

    public function test_value_with_special_characters(): void
    {
        $specialValue = 'discount:100%;limit:500';
        $this->discount->setValue($specialValue);
        $this->assertEquals($specialValue, $this->discount->getValue());
    }

    public function test_to_string_method(): void
    {
        $this->discount->setType(DiscountType::ORDER);
        $this->discount->setValue('100');

        // 当没有 ID 时，__toString 返回空字符串
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->discount);
    }

    public function test_to_string_method_with_freight_type(): void
    {
        $this->discount->setType(DiscountType::FREIGHT);
        $this->discount->setValue('50');

        // 当没有 ID 时，__toString 返回空字符串  
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->discount);
    }

    public function test_to_string_method_with_null_values(): void
    {
        // 当没有 ID 时，__toString 返回空字符串
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->discount);
    }

    public function test_retrieve_api_array(): void
    {
        $this->discount->setType(DiscountType::ORDER);
        $this->discount->setValue('150');
        $this->discount->setRemark('API test remark');

        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');
        $this->discount->setCreateTime($createTime);
        $this->discount->setUpdateTime($updateTime);

        $result = $this->discount->retrieveApiArray();
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('remark', $result);

        $this->assertEquals('order', $result['type']);
        $this->assertEquals('150', $result['value']);
        $this->assertEquals('API test remark', $result['remark']);
        $this->assertEquals('2023-01-01 10:00:00', $result['createTime']);
        $this->assertEquals('2023-01-02 11:00:00', $result['updateTime']);
    }

    public function test_retrieve_api_array_with_null_dates(): void
    {
        $this->discount->setType(DiscountType::FREIGHT);
        $this->discount->setValue('25');

        $result = $this->discount->retrieveApiArray();
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
    }

    public function test_retrieve_admin_array(): void
    {
        $this->discount->setType(DiscountType::ORDER);
        $this->discount->setValue('200');

        $result = $this->discount->retrieveAdminArray();
        $apiResult = $this->discount->retrieveApiArray();

        $this->assertEquals($apiResult, $result);
    }

    public function test_fluent_interface(): void
    {
        $result = $this->discount
            ->setType(DiscountType::FREIGHT)
            ->setValue('75')
            ->setRemark('fluent test')
            ->setCreatedBy('user')
            ->setUpdatedBy('updater')
            ->setCreatedFromIp('127.0.0.1')
            ->setUpdatedFromIp('127.0.0.2');

        $this->assertSame($this->discount, $result);
        $this->assertEquals(DiscountType::FREIGHT, $this->discount->getType());
        $this->assertEquals('75', $this->discount->getValue());
        $this->assertEquals('fluent test', $this->discount->getRemark());
    }

    public function test_null_values(): void
    {
        $this->discount->setRemark(null);
        $this->discount->setCreatedBy(null);
        $this->discount->setUpdatedBy(null);
        $this->discount->setCreatedFromIp(null);
        $this->discount->setUpdatedFromIp(null);

        $this->assertNull($this->discount->getRemark());
        $this->assertNull($this->discount->getCreatedBy());
        $this->assertNull($this->discount->getUpdatedBy());
        $this->assertNull($this->discount->getCreatedFromIp());
        $this->assertNull($this->discount->getUpdatedFromIp());
    }

    public function test_empty_string_values(): void
    {
        $this->discount->setValue('');
        $this->discount->setRemark('');
        $this->discount->setCreatedBy('');
        $this->discount->setUpdatedBy('');
        $this->discount->setCreatedFromIp('');
        $this->discount->setUpdatedFromIp('');

        $this->assertEquals('', $this->discount->getValue());
        $this->assertEquals('', $this->discount->getRemark());
        $this->assertEquals('', $this->discount->getCreatedBy());
        $this->assertEquals('', $this->discount->getUpdatedBy());
        $this->assertEquals('', $this->discount->getCreatedFromIp());
        $this->assertEquals('', $this->discount->getUpdatedFromIp());
    }

    public function test_ip_address_validation(): void
    {
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '127.0.0.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '::1',
        ];

        foreach ($validIps as $ip) {
            $this->discount->setCreatedFromIp($ip);
            $this->discount->setUpdatedFromIp($ip);

            $this->assertEquals($ip, $this->discount->getCreatedFromIp());
            $this->assertEquals($ip, $this->discount->getUpdatedFromIp());
        }
    }

    public function test_long_value_string(): void
    {
        $longValue = str_repeat('1', 1000);
        $this->discount->setValue($longValue);
        $this->assertEquals($longValue, $this->discount->getValue());
    }

    public function test_long_remark_string(): void
    {
        $longRemark = str_repeat('R', 1000);
        $this->discount->setRemark($longRemark);
        $this->assertEquals($longRemark, $this->discount->getRemark());
    }

    public function test_discount_type_consistency(): void
    {
        // 测试类型一致性
        $types = [DiscountType::ORDER, DiscountType::FREIGHT];

        foreach ($types as $type) {
            $this->discount->setType($type);
            $this->assertEquals($type, $this->discount->getType());
            $this->assertInstanceOf(DiscountType::class, $this->discount->getType());
        }
    }

    public function test_complex_value_scenarios(): void
    {
        $complexValues = [
            '{"type":"percentage","value":10}',
            'amount:100;threshold:500',
            'complex_rule_id_12345',
            'multi_line_value' . "\n" . 'second_line',
        ];

        foreach ($complexValues as $value) {
            $this->discount->setValue($value);
            $this->assertEquals($value, $this->discount->getValue());
        }
    }

    public function test_relationship_with_coupon_bidirectional(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Bidirectional Test Coupon');

        $this->discount->setCoupon($coupon);
        $coupon->addDiscount($this->discount);

        $this->assertSame($coupon, $this->discount->getCoupon());
        $this->assertTrue($coupon->getDiscounts()->contains($this->discount));
    }
} 