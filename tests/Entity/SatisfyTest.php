<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\Satisfy;
use Tourze\CouponCoreBundle\Enum\SatisfyType;

class SatisfyTest extends TestCase
{
    private Satisfy $satisfy;

    protected function setUp(): void
    {
        $this->satisfy = new Satisfy();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Satisfy::class, $this->satisfy);
        $this->assertNull($this->satisfy->getId());
    }

    public function test_getter_and_setter_methods(): void
    {
        $value = '100';
        $remark = 'Test satisfy condition';
        $createdBy = 'admin';
        $updatedBy = 'moderator';
        $createdFromIp = '192.168.1.1';
        $updatedFromIp = '192.168.1.2';

        $this->satisfy->setType(SatisfyType::ORDER_MONEY_GT);
        $this->satisfy->setValue($value);
        $this->satisfy->setRemark($remark);
        $this->satisfy->setCreatedBy($createdBy);
        $this->satisfy->setUpdatedBy($updatedBy);
        $this->satisfy->setCreatedFromIp($createdFromIp);
        $this->satisfy->setUpdatedFromIp($updatedFromIp);

        $this->assertEquals(SatisfyType::ORDER_MONEY_GT, $this->satisfy->getType());
        $this->assertEquals($value, $this->satisfy->getValue());
        $this->assertEquals($remark, $this->satisfy->getRemark());
        $this->assertEquals($createdBy, $this->satisfy->getCreatedBy());
        $this->assertEquals($updatedBy, $this->satisfy->getUpdatedBy());
        $this->assertEquals($createdFromIp, $this->satisfy->getCreatedFromIp());
        $this->assertEquals($updatedFromIp, $this->satisfy->getUpdatedFromIp());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->satisfy->setCreateTime($createTime);
        $this->satisfy->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->satisfy->getCreateTime());
        $this->assertEquals($updateTime, $this->satisfy->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->satisfy->setCreateTime(null);
        $this->satisfy->setUpdateTime(null);

        $this->assertNull($this->satisfy->getCreateTime());
        $this->assertNull($this->satisfy->getUpdateTime());
    }

    public function test_coupon_relationship(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $this->satisfy->setCoupon($coupon);

        $this->assertSame($coupon, $this->satisfy->getCoupon());
        $this->assertEquals('Test Coupon', $this->satisfy->getCoupon()->getName());
    }

    public function test_coupon_relationship_with_null(): void
    {
        $this->satisfy->setCoupon(null);

        $this->assertNull($this->satisfy->getCoupon());
    }

    public function test_satisfy_type_order_money_gt(): void
    {
        $this->satisfy->setType(SatisfyType::ORDER_MONEY_GT);
        $this->assertEquals(SatisfyType::ORDER_MONEY_GT, $this->satisfy->getType());
        $this->assertEquals('整单总金额大于', $this->satisfy->getType()->getLabel());
    }

    public function test_satisfy_type_order_money_lt(): void
    {
        $this->satisfy->setType(SatisfyType::ORDER_MONEY_LT);
        $this->assertEquals(SatisfyType::ORDER_MONEY_LT, $this->satisfy->getType());
        $this->assertEquals('整单总金额小于', $this->satisfy->getType()->getLabel());
    }

    public function test_satisfy_type_include_spu_category(): void
    {
        $this->satisfy->setType(SatisfyType::INCLUDE_SPU_CATEGORY);
        $this->assertEquals(SatisfyType::INCLUDE_SPU_CATEGORY, $this->satisfy->getType());
        $this->assertEquals('包含指定品类', $this->satisfy->getType()->getLabel());
    }

    public function test_satisfy_type_include_spu(): void
    {
        $this->satisfy->setType(SatisfyType::INCLUDE_SPU);
        $this->assertEquals(SatisfyType::INCLUDE_SPU, $this->satisfy->getType());
        $this->assertEquals('包含指定SPU', $this->satisfy->getType()->getLabel());
    }

    public function test_satisfy_type_include_sku(): void
    {
        $this->satisfy->setType(SatisfyType::INCLUDE_SKU);
        $this->assertEquals(SatisfyType::INCLUDE_SKU, $this->satisfy->getType());
        $this->assertEquals('包含指定SKU', $this->satisfy->getType()->getLabel());
    }

    public function test_satisfy_type_gather_day_gt(): void
    {
        $this->satisfy->setType(SatisfyType::GATHER_DAY_GT);
        $this->assertEquals(SatisfyType::GATHER_DAY_GT, $this->satisfy->getType());
        $this->assertEquals('领取天数大于', $this->satisfy->getType()->getLabel());
    }

    public function test_value_with_numeric_string(): void
    {
        $numericValue = '9999';
        $this->satisfy->setValue($numericValue);
        
        $this->assertEquals($numericValue, $this->satisfy->getValue());
        $this->assertTrue(is_numeric($this->satisfy->getValue()));
    }

    public function test_value_with_decimal_string(): void
    {
        $decimalValue = '99.99';
        $this->satisfy->setValue($decimalValue);
        
        $this->assertEquals($decimalValue, $this->satisfy->getValue());
        $this->assertTrue(is_numeric($this->satisfy->getValue()));
    }

    public function test_value_with_empty_string(): void
    {
        $this->satisfy->setValue('');
        $this->assertEquals('', $this->satisfy->getValue());
    }

    public function test_value_with_json_string(): void
    {
        $jsonValue = '["sku1", "sku2", "sku3"]';
        $this->satisfy->setValue($jsonValue);
        
        $this->assertEquals($jsonValue, $this->satisfy->getValue());
        $this->assertJson($this->satisfy->getValue());
    }

    public function test_value_with_category_ids(): void
    {
        $categoryIds = '1,2,3,4,5';
        $this->satisfy->setValue($categoryIds);
        
        $this->assertEquals($categoryIds, $this->satisfy->getValue());
    }

    public function test_to_string_method(): void
    {
        $this->satisfy->setType(SatisfyType::ORDER_MONEY_GT);
        $this->satisfy->setValue('100');

        $expected = '整单总金额大于: 100';
        $this->assertEquals($expected, (string) $this->satisfy);
    }

    public function test_to_string_method_with_order_money_lt(): void
    {
        $this->satisfy->setType(SatisfyType::ORDER_MONEY_LT);
        $this->satisfy->setValue('500');

        $expected = '整单总金额小于: 500';
        $this->assertEquals($expected, (string) $this->satisfy);
    }

    public function test_to_string_method_with_include_spu_category(): void
    {
        $this->satisfy->setType(SatisfyType::INCLUDE_SPU_CATEGORY);
        $this->satisfy->setValue('1,2,3');

        $expected = '包含指定品类: 1,2,3';
        $this->assertEquals($expected, (string) $this->satisfy);
    }

    public function test_to_string_method_with_null_values(): void
    {
        $this->satisfy->setType(SatisfyType::ORDER_MONEY_GT);
        $this->satisfy->setValue('');

        $expected = '整单总金额大于: ';
        $this->assertEquals($expected, (string) $this->satisfy);
    }

    public function test_retrieve_api_array(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->satisfy->setType(SatisfyType::ORDER_MONEY_GT);
        $this->satisfy->setValue('100');
        $this->satisfy->setRemark('测试备注');
        $this->satisfy->setCreateTime($createTime);
        $this->satisfy->setUpdateTime($updateTime);

        $result = $this->satisfy->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('remark', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);

        $this->assertEquals('order-money-gt', $result['type']);
        $this->assertEquals('100', $result['value']);
        $this->assertEquals('测试备注', $result['remark']);
        $this->assertEquals('2023-01-01 10:00:00', $result['createTime']);
        $this->assertEquals('2023-01-02 11:00:00', $result['updateTime']);
    }

    public function test_retrieve_api_array_with_null_dates(): void
    {
        $this->satisfy->setType(SatisfyType::INCLUDE_SKU);
        $this->satisfy->setValue('sku123');
        $this->satisfy->setCreateTime(null);
        $this->satisfy->setUpdateTime(null);

        $result = $this->satisfy->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
    }

    public function test_retrieve_admin_array(): void
    {
        $this->satisfy->setType(SatisfyType::GATHER_DAY_GT);
        $this->satisfy->setValue('7');
        
        $apiArray = $this->satisfy->retrieveApiArray();
        $adminArray = $this->satisfy->retrieveAdminArray();

        $this->assertEquals($apiArray, $adminArray);
    }

    public function test_fluent_interface(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Fluent Test Coupon');

        $result = $this->satisfy
            ->setCoupon($coupon)
            ->setType(SatisfyType::ORDER_MONEY_GT)
            ->setValue('200')
            ->setRemark('Fluent test')
            ->setCreatedBy('user')
            ->setUpdatedBy('updater');

        $this->assertSame($this->satisfy, $result);
        $this->assertSame($coupon, $this->satisfy->getCoupon());
        $this->assertEquals(SatisfyType::ORDER_MONEY_GT, $this->satisfy->getType());
        $this->assertEquals('200', $this->satisfy->getValue());
        $this->assertEquals('Fluent test', $this->satisfy->getRemark());
    }

    public function test_null_values(): void
    {
        $this->satisfy->setRemark(null);
        $this->satisfy->setCreatedBy(null);
        $this->satisfy->setUpdatedBy(null);
        $this->satisfy->setCreatedFromIp(null);
        $this->satisfy->setUpdatedFromIp(null);

        $this->assertNull($this->satisfy->getRemark());
        $this->assertNull($this->satisfy->getCreatedBy());
        $this->assertNull($this->satisfy->getUpdatedBy());
        $this->assertNull($this->satisfy->getCreatedFromIp());
        $this->assertNull($this->satisfy->getUpdatedFromIp());
    }

    public function test_empty_string_values(): void
    {
        $this->satisfy->setValue('');
        $this->satisfy->setRemark('');
        $this->satisfy->setCreatedBy('');
        $this->satisfy->setUpdatedBy('');
        $this->satisfy->setCreatedFromIp('');
        $this->satisfy->setUpdatedFromIp('');

        $this->assertEquals('', $this->satisfy->getValue());
        $this->assertEquals('', $this->satisfy->getRemark());
        $this->assertEquals('', $this->satisfy->getCreatedBy());
        $this->assertEquals('', $this->satisfy->getUpdatedBy());
        $this->assertEquals('', $this->satisfy->getCreatedFromIp());
        $this->assertEquals('', $this->satisfy->getUpdatedFromIp());
    }

    public function test_satisfy_type_consistency(): void
    {
        // 测试所有 SatisfyType 枚举值
        $allTypes = [
            SatisfyType::ORDER_MONEY_GT,
            SatisfyType::ORDER_MONEY_LT,
            SatisfyType::INCLUDE_SPU_CATEGORY,
            SatisfyType::INCLUDE_SPU,
            SatisfyType::INCLUDE_SKU,
            SatisfyType::GATHER_DAY_GT,
        ];

        foreach ($allTypes as $type) {
            $this->satisfy->setType($type);
            $this->assertEquals($type, $this->satisfy->getType());
            $this->assertNotEmpty($type->getLabel());
        }
    }

    public function test_business_logic_money_conditions(): void
    {
        // 测试金额条件的业务逻辑
        $moneyConditions = [
            ['type' => SatisfyType::ORDER_MONEY_GT, 'value' => '99.99', 'description' => '订单满100元使用'],
            ['type' => SatisfyType::ORDER_MONEY_LT, 'value' => '1000.00', 'description' => '订单小于1000元使用'],
        ];

        foreach ($moneyConditions as $condition) {
            $this->satisfy->setType($condition['type']);
            $this->satisfy->setValue($condition['value']);
            $this->satisfy->setRemark($condition['description']);

            $this->assertEquals($condition['type'], $this->satisfy->getType());
            $this->assertEquals($condition['value'], $this->satisfy->getValue());
            $this->assertTrue(is_numeric($this->satisfy->getValue()));
        }
    }

    public function test_relationship_with_coupon_bidirectional(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Bidirectional Test');
        
        $this->satisfy->setCoupon($coupon);
        $coupon->addSatisfy($this->satisfy);

        $this->assertSame($coupon, $this->satisfy->getCoupon());
        $this->assertTrue($coupon->getSatisfies()->contains($this->satisfy));
    }

    public function test_ip_address_validation(): void
    {
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '127.0.0.1',
            '::1',
            '2001:db8::1',
        ];

        foreach ($validIps as $ip) {
            $this->satisfy->setCreatedFromIp($ip);
            $this->satisfy->setUpdatedFromIp($ip);

            $this->assertEquals($ip, $this->satisfy->getCreatedFromIp());
            $this->assertEquals($ip, $this->satisfy->getUpdatedFromIp());
        }
    }

    public function test_complex_satisfy_scenarios(): void
    {
        // 复杂的使用条件组合场景
        $scenarios = [
            [
                'type' => SatisfyType::INCLUDE_SPU_CATEGORY,
                'value' => '1,3,5,7',
                'remark' => '包含指定品类：服装、数码、家电、食品',
            ],
            [
                'type' => SatisfyType::INCLUDE_SKU,
                'value' => '["SKU001", "SKU002", "SKU003"]',
                'remark' => '包含指定商品SKU列表',
            ],
            [
                'type' => SatisfyType::GATHER_DAY_GT,
                'value' => '30',
                'remark' => '领取超过30天才能使用',
            ],
        ];

        foreach ($scenarios as $scenario) {
            $satisfy = new Satisfy();
            $satisfy->setType($scenario['type']);
            $satisfy->setValue($scenario['value']);
            $satisfy->setRemark($scenario['remark']);

            $this->assertEquals($scenario['type'], $satisfy->getType());
            $this->assertEquals($scenario['value'], $satisfy->getValue());
            $this->assertEquals($scenario['remark'], $satisfy->getRemark());
        }
    }
} 