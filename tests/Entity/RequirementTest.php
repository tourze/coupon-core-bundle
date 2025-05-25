<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\Requirement;
use Tourze\CouponCoreBundle\Enum\RequirementType;

class RequirementTest extends TestCase
{
    private Requirement $requirement;

    protected function setUp(): void
    {
        $this->requirement = new Requirement();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Requirement::class, $this->requirement);
        $this->assertNull($this->requirement->getId());
        $this->assertNull($this->requirement->getCoupon());
        $this->assertNull($this->requirement->getType());
        $this->assertNull($this->requirement->getValue());
    }

    public function test_getter_and_setter_methods(): void
    {
        $type = RequirementType::REG_DAY_LT;
        $value = '30';
        $remark = 'Test requirement remark';
        $createdBy = 'admin';
        $updatedBy = 'moderator';
        $createdFromIp = '192.168.1.1';
        $updatedFromIp = '192.168.1.2';

        $this->requirement->setType($type);
        $this->requirement->setValue($value);
        $this->requirement->setRemark($remark);
        $this->requirement->setCreatedBy($createdBy);
        $this->requirement->setUpdatedBy($updatedBy);
        $this->requirement->setCreatedFromIp($createdFromIp);
        $this->requirement->setUpdatedFromIp($updatedFromIp);

        $this->assertEquals($type, $this->requirement->getType());
        $this->assertEquals($value, $this->requirement->getValue());
        $this->assertEquals($remark, $this->requirement->getRemark());
        $this->assertEquals($createdBy, $this->requirement->getCreatedBy());
        $this->assertEquals($updatedBy, $this->requirement->getUpdatedBy());
        $this->assertEquals($createdFromIp, $this->requirement->getCreatedFromIp());
        $this->assertEquals($updatedFromIp, $this->requirement->getUpdatedFromIp());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->requirement->setCreateTime($createTime);
        $this->requirement->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->requirement->getCreateTime());
        $this->assertEquals($updateTime, $this->requirement->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->requirement->setCreateTime(null);
        $this->requirement->setUpdateTime(null);

        $this->assertNull($this->requirement->getCreateTime());
        $this->assertNull($this->requirement->getUpdateTime());
    }

    public function test_coupon_relationship(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $this->requirement->setCoupon($coupon);

        $this->assertSame($coupon, $this->requirement->getCoupon());
    }

    public function test_coupon_relationship_with_null(): void
    {
        $this->requirement->setCoupon(null);

        $this->assertNull($this->requirement->getCoupon());
    }

    public function test_requirement_type_reg_day_lt(): void
    {
        $this->requirement->setType(RequirementType::REG_DAY_LT);
        $this->assertEquals(RequirementType::REG_DAY_LT, $this->requirement->getType());
    }

    public function test_requirement_type_reg_day_gt(): void
    {
        $this->requirement->setType(RequirementType::REG_DAY_GT);
        $this->assertEquals(RequirementType::REG_DAY_GT, $this->requirement->getType());
    }

    public function test_requirement_type_total_gather_count(): void
    {
        $this->requirement->setType(RequirementType::TOTAL_GATHER_COUNT);
        $this->assertEquals(RequirementType::TOTAL_GATHER_COUNT, $this->requirement->getType());
    }

    public function test_value_with_numeric_string(): void
    {
        $numericValues = ['0', '1', '7', '30', '90', '365'];

        foreach ($numericValues as $value) {
            $this->requirement->setValue($value);
            $this->assertEquals($value, $this->requirement->getValue());
        }
    }

    public function test_value_with_empty_string(): void
    {
        $this->requirement->setValue('');
        $this->assertEquals('', $this->requirement->getValue());
    }

    public function test_value_with_complex_rules(): void
    {
        $complexRules = [
            '{"min":1,"max":10}',
            'user_level:vip',
            'region:beijing,shanghai',
        ];

        foreach ($complexRules as $rule) {
            $this->requirement->setValue($rule);
            $this->assertEquals($rule, $this->requirement->getValue());
        }
    }

    public function test_to_string_method(): void
    {
        $this->requirement->setType(RequirementType::REG_DAY_LT);
        $this->requirement->setValue('30');

        // 当没有 ID 时，__toString 返回空字符串
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->requirement);
    }

    public function test_to_string_method_with_reg_day_gt(): void
    {
        $this->requirement->setType(RequirementType::REG_DAY_GT);
        $this->requirement->setValue('7');

        // 当没有 ID 时，__toString 返回空字符串
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->requirement);
    }

    public function test_to_string_method_with_total_gather_count(): void
    {
        $this->requirement->setType(RequirementType::TOTAL_GATHER_COUNT);
        $this->requirement->setValue('5');

        // 当没有 ID 时，__toString 返回空字符串
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->requirement);
    }

    public function test_to_string_method_with_null_values(): void
    {
        // 当没有 ID 时，__toString 返回空字符串
        $expectedString = '';
        $this->assertEquals($expectedString, (string) $this->requirement);
    }

    public function test_retrieve_api_array(): void
    {
        $this->requirement->setType(RequirementType::REG_DAY_LT);
        $this->requirement->setValue('30');
        $this->requirement->setRemark('API test remark');

        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');
        $this->requirement->setCreateTime($createTime);
        $this->requirement->setUpdateTime($updateTime);

        $result = $this->requirement->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('remark', $result);

        $this->assertEquals('reg-day-lt', $result['type']);
        $this->assertEquals('30', $result['value']);
        $this->assertEquals('API test remark', $result['remark']);
        $this->assertEquals('2023-01-01 10:00:00', $result['createTime']);
        $this->assertEquals('2023-01-02 11:00:00', $result['updateTime']);
    }

    public function test_retrieve_api_array_with_null_dates(): void
    {
        $this->requirement->setType(RequirementType::TOTAL_GATHER_COUNT);
        $this->requirement->setValue('3');

        $result = $this->requirement->retrieveApiArray();

        $this->assertIsArray($result);
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
    }

    public function test_retrieve_admin_array(): void
    {
        $this->requirement->setType(RequirementType::REG_DAY_GT);
        $this->requirement->setValue('10');

        $result = $this->requirement->retrieveAdminArray();
        $apiResult = $this->requirement->retrieveApiArray();

        $this->assertEquals($apiResult, $result);
    }

    public function test_fluent_interface(): void
    {
        $result = $this->requirement
            ->setType(RequirementType::REG_DAY_LT)
            ->setValue('15')
            ->setRemark('fluent test')
            ->setCreatedBy('user')
            ->setUpdatedBy('updater')
            ->setCreatedFromIp('127.0.0.1')
            ->setUpdatedFromIp('127.0.0.2');

        $this->assertSame($this->requirement, $result);
        $this->assertEquals(RequirementType::REG_DAY_LT, $this->requirement->getType());
        $this->assertEquals('15', $this->requirement->getValue());
        $this->assertEquals('fluent test', $this->requirement->getRemark());
    }

    public function test_null_values(): void
    {
        $this->requirement->setRemark(null);
        $this->requirement->setCreatedBy(null);
        $this->requirement->setUpdatedBy(null);
        $this->requirement->setCreatedFromIp(null);
        $this->requirement->setUpdatedFromIp(null);

        $this->assertNull($this->requirement->getRemark());
        $this->assertNull($this->requirement->getCreatedBy());
        $this->assertNull($this->requirement->getUpdatedBy());
        $this->assertNull($this->requirement->getCreatedFromIp());
        $this->assertNull($this->requirement->getUpdatedFromIp());
    }

    public function test_empty_string_values(): void
    {
        $this->requirement->setValue('');
        $this->requirement->setRemark('');
        $this->requirement->setCreatedBy('');
        $this->requirement->setUpdatedBy('');
        $this->requirement->setCreatedFromIp('');
        $this->requirement->setUpdatedFromIp('');

        $this->assertEquals('', $this->requirement->getValue());
        $this->assertEquals('', $this->requirement->getRemark());
        $this->assertEquals('', $this->requirement->getCreatedBy());
        $this->assertEquals('', $this->requirement->getUpdatedBy());
        $this->assertEquals('', $this->requirement->getCreatedFromIp());
        $this->assertEquals('', $this->requirement->getUpdatedFromIp());
    }

    public function test_requirement_type_consistency(): void
    {
        $types = [
            RequirementType::REG_DAY_LT,
            RequirementType::REG_DAY_GT,
            RequirementType::TOTAL_GATHER_COUNT,
        ];

        foreach ($types as $type) {
            $this->requirement->setType($type);
            $this->assertEquals($type, $this->requirement->getType());
            $this->assertInstanceOf(RequirementType::class, $this->requirement->getType());
        }
    }

    public function test_business_logic_values(): void
    {
        // 测试注册天数限制的常见值
        $regDayValues = ['1', '7', '15', '30', '60', '90', '180', '365'];

        foreach ($regDayValues as $days) {
            $this->requirement->setType(RequirementType::REG_DAY_LT);
            $this->requirement->setValue($days);
            $this->assertEquals($days, $this->requirement->getValue());
        }

        // 测试领取上限的常见值
        $gatherCountValues = ['1', '2', '3', '5', '10', '50', '100'];

        foreach ($gatherCountValues as $count) {
            $this->requirement->setType(RequirementType::TOTAL_GATHER_COUNT);
            $this->requirement->setValue($count);
            $this->assertEquals($count, $this->requirement->getValue());
        }
    }

    public function test_relationship_with_coupon_bidirectional(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Bidirectional Test Coupon');

        $this->requirement->setCoupon($coupon);
        $coupon->addRequirement($this->requirement);

        $this->assertSame($coupon, $this->requirement->getCoupon());
        $this->assertTrue($coupon->getRequirements()->contains($this->requirement));
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
            $this->requirement->setCreatedFromIp($ip);
            $this->requirement->setUpdatedFromIp($ip);

            $this->assertEquals($ip, $this->requirement->getCreatedFromIp());
            $this->assertEquals($ip, $this->requirement->getUpdatedFromIp());
        }
    }

    public function test_complex_requirement_scenarios(): void
    {
        // 模拟复杂业务场景的需求配置
        $complexScenarios = [
            [
                'type' => RequirementType::REG_DAY_LT,
                'value' => '30',
                'description' => '新用户30天内有效',
            ],
            [
                'type' => RequirementType::REG_DAY_GT,
                'value' => '90',
                'description' => '老用户90天以上',
            ],
            [
                'type' => RequirementType::TOTAL_GATHER_COUNT,
                'value' => '1',
                'description' => '限领一次',
            ],
        ];

        foreach ($complexScenarios as $scenario) {
            $this->requirement->setType($scenario['type']);
            $this->requirement->setValue($scenario['value']);
            $this->requirement->setRemark($scenario['description']);

            $this->assertEquals($scenario['type'], $this->requirement->getType());
            $this->assertEquals($scenario['value'], $this->requirement->getValue());
            $this->assertEquals($scenario['description'], $this->requirement->getRemark());
        }
    }
} 