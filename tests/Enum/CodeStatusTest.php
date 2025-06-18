<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CodeStatus;

/**
 * 优惠券码状态枚举测试
 */
class CodeStatusTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('unused', CodeStatus::UNUSED->value);
        $this->assertEquals('used', CodeStatus::USED->value);
        $this->assertEquals('invalid', CodeStatus::INVALID->value);
        $this->assertEquals('expired', CodeStatus::EXPIRED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('未使用', CodeStatus::UNUSED->getLabel());
        $this->assertEquals('已使用', CodeStatus::USED->getLabel());
        $this->assertEquals('无效', CodeStatus::INVALID->getLabel());
        $this->assertEquals('已过期', CodeStatus::EXPIRED->getLabel());
    }

    public function testFromValue(): void
    {
        $this->assertEquals(CodeStatus::UNUSED, CodeStatus::from('unused'));
        $this->assertEquals(CodeStatus::USED, CodeStatus::from('used'));
        $this->assertEquals(CodeStatus::INVALID, CodeStatus::from('invalid'));
        $this->assertEquals(CodeStatus::EXPIRED, CodeStatus::from('expired'));
    }

    public function testTryFromValue(): void
    {
        $this->assertEquals(CodeStatus::UNUSED, CodeStatus::tryFrom('unused'));
        $this->assertEquals(CodeStatus::USED, CodeStatus::tryFrom('used'));
        $this->assertEquals(CodeStatus::INVALID, CodeStatus::tryFrom('invalid'));
        $this->assertEquals(CodeStatus::EXPIRED, CodeStatus::tryFrom('expired'));
        $this->assertNull(CodeStatus::tryFrom('invalid_status'));
    }

    public function testGetCases(): void
    {
        $cases = CodeStatus::cases();
        $this->assertCount(4, $cases);
        $this->assertContains(CodeStatus::UNUSED, $cases);
        $this->assertContains(CodeStatus::USED, $cases);
        $this->assertContains(CodeStatus::INVALID, $cases);
        $this->assertContains(CodeStatus::EXPIRED, $cases);
    }

    public function testItemTrait(): void
    {
        // 测试 ItemTrait 提供的方法
        $item = CodeStatus::UNUSED->toSelectItem();
        $this->assertArrayHasKey('value', $item);
        $this->assertArrayHasKey('label', $item);
        $this->assertArrayHasKey('text', $item);
        $this->assertArrayHasKey('name', $item);
        
        $this->assertEquals('unused', $item['value']);
        $this->assertEquals('未使用', $item['label']);
        $this->assertEquals('未使用', $item['text']);
        $this->assertEquals('未使用', $item['name']);
        
        // 测试 toArray 方法
        $array = CodeStatus::USED->toArray();
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals('used', $array['value']);
        $this->assertEquals('已使用', $array['label']);
    }

    public function testSelectTrait(): void
    {
        // 测试 SelectTrait 提供的方法
        $options = CodeStatus::genOptions();
        $this->assertCount(4, $options);
        
        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('text', $option);
            $this->assertArrayHasKey('name', $option);
        }
        
        // 验证第一个选项
        $firstOption = $options[0];
        $this->assertEquals('unused', $firstOption['value']);
        $this->assertEquals('未使用', $firstOption['label']);
    }
}
