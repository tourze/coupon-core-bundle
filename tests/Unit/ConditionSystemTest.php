<?php

namespace Tourze\CouponCoreBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\RegisterDaysRequirement;
use Tourze\CouponCoreBundle\Handler\RegisterDaysRequirementHandler;
use Tourze\CouponCoreBundle\ValueObject\ValidationResult;

/**
 * 条件系统单元测试
 */
class ConditionSystemTest extends TestCase
{
    public function testRegisterDaysRequirementHandler(): void
    {
        $handler = new RegisterDaysRequirementHandler();
        
        // 测试基本信息
        $this->assertEquals('register_days', $handler->getType());
        $this->assertEquals('注册天数限制', $handler->getLabel());
        $this->assertNotEmpty($handler->getDescription());
        
        // 测试表单字段
        $formFields = $handler->getFormFields();
        $this->assertIsIterable($formFields);

        $fieldArray = iterator_to_array($formFields);
        $this->assertCount(2, $fieldArray);

        // 检查第一个字段
        $firstField = $fieldArray[0];
        $this->assertInstanceOf(\Tourze\CouponCoreBundle\ValueObject\FormField::class, $firstField);
        $this->assertEquals('minDays', $firstField->getName());
        $this->assertEquals('integer', $firstField->getType());
        $this->assertTrue($firstField->isRequired());

        // 测试配置验证
        $validConfig = ['minDays' => 7, 'maxDays' => 30];
        $result = $handler->validateConfig($validConfig);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        
        // 测试无效配置
        $invalidConfig = ['minDays' => -1];
        $result = $handler->validateConfig($invalidConfig);
        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
    }

    public function testRegisterDaysRequirementEntity(): void
    {
        $requirement = new RegisterDaysRequirement();
        $requirement->setType('register_days');
        $requirement->setLabel('注册天数限制');
        $requirement->setMinDays(7);
        $requirement->setMaxDays(30);
        $requirement->setEnabled(true);
        
        // 测试getter方法
        $this->assertEquals('register_days', $requirement->getType());
        $this->assertEquals('注册天数限制', $requirement->getLabel());
        $this->assertEquals(7, $requirement->getMinDays());
        $this->assertEquals(30, $requirement->getMaxDays());
        $this->assertTrue($requirement->isEnabled());
        
        // 测试toArray方法
        $array = $requirement->toArray();
        $this->assertIsArray($array);
        $this->assertEquals('register_days', $array['type']);
        $this->assertEquals(7, $array['minDays']);
        $this->assertEquals(30, $array['maxDays']);
    }

    public function testConditionCreation(): void
    {
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        
        $handler = new RegisterDaysRequirementHandler();
        $config = ['minDays' => 7, 'maxDays' => 30];
        
        $condition = $handler->createCondition($coupon, $config);
        
        $this->assertInstanceOf(RegisterDaysRequirement::class, $condition);
        $this->assertEquals($coupon, $condition->getCoupon());
        $this->assertEquals('register_days', $condition->getType());
        
        // 转换为具体类型后再调用方法
        /** @var RegisterDaysRequirement $requirement */
        $requirement = $condition;
        $this->assertEquals(7, $requirement->getMinDays());
        $this->assertEquals(30, $requirement->getMaxDays());
    }

    public function testDisplayText(): void
    {
        $requirement = new RegisterDaysRequirement();
        $requirement->setMinDays(7);
        $requirement->setMaxDays(30);
        
        $handler = new RegisterDaysRequirementHandler();
        $displayText = $handler->getDisplayText($requirement);
        
        $this->assertStringContainsString('注册满7天', $displayText);
        $this->assertStringContainsString('不超过30天', $displayText);
    }

    public function testValidationResultSuccess(): void
    {
        $result = ValidationResult::success();
        
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
        $this->assertNull($result->getFirstError());
    }

    public function testValidationResultFailure(): void
    {
        $errors = ['错误1', '错误2'];
        $result = ValidationResult::failure($errors);
        
        $this->assertFalse($result->isValid());
        $this->assertEquals($errors, $result->getErrors());
        $this->assertEquals('错误1', $result->getFirstError());
    }
}
