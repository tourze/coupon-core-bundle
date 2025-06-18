<?php

namespace Tourze\CouponCoreBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\DiscountType;

class DiscountTypeTest extends TestCase
{
    public function test_enum_values(): void
    {
        $this->assertEquals('freight', DiscountType::FREIGHT->value);
        $this->assertEquals('order', DiscountType::ORDER->value);
    }

    public function test_enum_labels(): void
    {
        $this->assertEquals('抵扣运费', DiscountType::FREIGHT->getLabel());
        $this->assertEquals('整单抵扣', DiscountType::ORDER->getLabel());
    }

    public function test_all_cases_have_labels(): void
    {
        $cases = DiscountType::cases();
        
        $this->assertCount(2, $cases);
        
        foreach ($cases as $case) {
            $this->assertNotEmpty($case->getLabel(), "Case {$case->value} should have a non-empty label");
        }
    }

    public function test_to_select_item(): void
    {
        $freightItem = DiscountType::FREIGHT->toSelectItem();
        $this->assertArrayHasKey('label', $freightItem);
        $this->assertArrayHasKey('text', $freightItem);
        $this->assertArrayHasKey('value', $freightItem);
        $this->assertArrayHasKey('name', $freightItem);
        $this->assertEquals('抵扣运费', $freightItem['label']);
        $this->assertEquals('抵扣运费', $freightItem['text']);
        $this->assertEquals('freight', $freightItem['value']);
        $this->assertEquals('抵扣运费', $freightItem['name']);
        
        $orderItem = DiscountType::ORDER->toSelectItem();
        $this->assertArrayHasKey('label', $orderItem);
        $this->assertArrayHasKey('text', $orderItem);
        $this->assertArrayHasKey('value', $orderItem);
        $this->assertArrayHasKey('name', $orderItem);
        $this->assertEquals('整单抵扣', $orderItem['label']);
        $this->assertEquals('整单抵扣', $orderItem['text']);
        $this->assertEquals('order', $orderItem['value']);
        $this->assertEquals('整单抵扣', $orderItem['name']);
    }

    public function test_to_array(): void
    {
        $freightArray = DiscountType::FREIGHT->toArray();
        $this->assertArrayHasKey('value', $freightArray);
        $this->assertArrayHasKey('label', $freightArray);
        $this->assertEquals('freight', $freightArray['value']);
        $this->assertEquals('抵扣运费', $freightArray['label']);
        
        $orderArray = DiscountType::ORDER->toArray();
        $this->assertArrayHasKey('value', $orderArray);
        $this->assertArrayHasKey('label', $orderArray);
        $this->assertEquals('order', $orderArray['value']);
        $this->assertEquals('整单抵扣', $orderArray['label']);
    }

    public function test_gen_options(): void
    {
        $options = DiscountType::genOptions();
        $this->assertCount(2, $options);
        
        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('text', $option);
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('name', $option);
            $this->assertNotEmpty($option['label']);
            $this->assertNotEmpty($option['value']);
        }
        
        // 验证具体选项
        $expectedOptions = [
            [
                'label' => '抵扣运费',
                'text' => '抵扣运费',
                'value' => 'freight',
                'name' => '抵扣运费',
            ],
            [
                'label' => '整单抵扣',
                'text' => '整单抵扣',
                'value' => 'order',
                'name' => '整单抵扣',
            ],
        ];
        
        $this->assertEquals($expectedOptions, $options);
    }

    public function test_from_value(): void
    {
        $this->assertEquals(DiscountType::FREIGHT, DiscountType::from('freight'));
        $this->assertEquals(DiscountType::ORDER, DiscountType::from('order'));
    }

    public function test_try_from_value(): void
    {
        $this->assertEquals(DiscountType::FREIGHT, DiscountType::tryFrom('freight'));
        $this->assertEquals(DiscountType::ORDER, DiscountType::tryFrom('order'));
        $this->assertNull(DiscountType::tryFrom('nonexistent'));
    }

    public function test_case_consistency(): void
    {
        $cases = DiscountType::cases();
        $expectedCases = [
            DiscountType::FREIGHT,
            DiscountType::ORDER,
        ];
        
        $this->assertEquals($expectedCases, $cases);
    }

    public function test_business_logic_discount_types(): void
    {
        // 测试运费抵扣类型
        $freightType = DiscountType::FREIGHT;
        $this->assertEquals('抵扣运费', $freightType->getLabel());
        $this->assertEquals('freight', $freightType->value);
        
        // 测试整单抵扣类型
        $orderType = DiscountType::ORDER;
        $this->assertEquals('整单抵扣', $orderType->getLabel());
        $this->assertEquals('order', $orderType->value);
    }

    public function test_discount_type_string_representation(): void
    {
        $this->assertEquals('freight', (string) DiscountType::FREIGHT->value);
        $this->assertEquals('order', (string) DiscountType::ORDER->value);
    }

    public function test_label_localization(): void
    {
        // 测试标签的本地化（中文）
        $labels = [
            DiscountType::FREIGHT->getLabel(),
            DiscountType::ORDER->getLabel(),
        ];
        
        foreach ($labels as $label) {
            $this->assertNotEmpty($label);
            // 检查是否包含中文字符
            $this->assertMatchesRegularExpression('/[\x{4e00}-\x{9fff}]/u', $label);
        }
    }

    public function test_enum_comparison(): void
    {
        $type1 = DiscountType::FREIGHT;
        $type2 = DiscountType::FREIGHT;
        $type3 = DiscountType::ORDER;
        
        $this->assertTrue($type1 === $type2);
        $this->assertFalse($type1 === $type3);
        $this->assertTrue($type1 !== $type3);
    }

    public function test_invalid_type_handling(): void
    {
        $this->expectException(\ValueError::class);
        DiscountType::from('invalid_type');
    }

    public function test_serialization(): void
    {
        $type = DiscountType::ORDER;
        $serialized = serialize($type);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($type, $unserialized);
        $this->assertEquals($type->value, $unserialized->value);
        $this->assertEquals($type->getLabel(), $unserialized->getLabel());
    }

    public function test_json_serialization(): void
    {
        $type = DiscountType::FREIGHT;
        $json = json_encode(['type' => $type->value]);
        $decoded = json_decode($json, true);
        
        $this->assertEquals('freight', $decoded['type']);
        
        $reconstructedType = DiscountType::from($decoded['type']);
        $this->assertEquals($type, $reconstructedType);
    }

    public function test_business_scenarios(): void
    {
        // 运费抵扣场景
        $freightDiscount = DiscountType::FREIGHT;
        $this->assertEquals('freight', $freightDiscount->value);
        $this->assertStringContainsString('运费', $freightDiscount->getLabel());
        
        // 整单抵扣场景
        $orderDiscount = DiscountType::ORDER;
        $this->assertEquals('order', $orderDiscount->value);
        $this->assertStringContainsString('整单', $orderDiscount->getLabel());
    }

    public function test_enum_switch_statement(): void
    {
        $types = DiscountType::cases();
        
        foreach ($types as $type) {
            $result = match ($type) {
                DiscountType::FREIGHT => 'freight_handling',
                DiscountType::ORDER => 'order_handling',
            };
            
            $this->assertNotEmpty($result);
            $this->assertContains($result, ['freight_handling', 'order_handling']);
        }
    }

    public function test_business_validation(): void
    {
        // 测试业务场景的有效性验证
        $freightType = DiscountType::FREIGHT;
        $orderType = DiscountType::ORDER;
        
        // 运费抵扣应该用于运费相关业务
        $this->assertTrue($freightType->value === 'freight');
        
        // 整单抵扣应该用于整单价格抵扣
        $this->assertTrue($orderType->value === 'order');
    }

    public function test_type_categorization(): void
    {
        // 测试折扣类型的分类
        $types = DiscountType::cases();
        
        $freightTypes = array_filter($types, fn($type) => $type === DiscountType::FREIGHT);
        $orderTypes = array_filter($types, fn($type) => $type === DiscountType::ORDER);
        
        $this->assertCount(1, $freightTypes);
        $this->assertCount(1, $orderTypes);
        $this->assertCount(2, $types);
    }

    public function test_comprehensive_coverage(): void
    {
        // 确保所有枚举值都被测试覆盖
        $cases = DiscountType::cases();
        $values = array_map(fn($case) => $case->value, $cases);
        $labels = array_map(fn($case) => $case->getLabel(), $cases);
        
        $this->assertEquals(['freight', 'order'], $values);
        $this->assertEquals(['抵扣运费', '整单抵扣'], $labels);
        
        // 确保每个case都有对应的测试
        foreach ($cases as $case) {
            $this->assertNotNull($case->getLabel());
            $this->assertNotEmpty($case->value);
            $this->assertIsArray($case->toSelectItem());
            $this->assertIsArray($case->toArray());
        }
    }
} 