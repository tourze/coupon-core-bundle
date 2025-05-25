# 优惠券条件系统

## 概述

新的优惠券条件系统采用插件化架构，支持灵活扩展各种条件类型。系统分为领取条件（Requirement）和使用条件（Satisfy）两大类。

## 核心特性

- **插件化架构**：新增条件类型只需实现接口并创建实体
- **类型安全**：使用JOINED继承，每种条件有独立的数据表
- **动态表单**：后台自动生成对应的表单字段
- **完全扩展**：无需修改核心代码即可添加新条件

## 系统架构

### 核心接口

- `ConditionHandlerInterface`：条件处理器基础接口
- `RequirementHandlerInterface`：领取条件处理器接口
- `SatisfyHandlerInterface`：使用条件处理器接口

### 实体设计

- `BaseCondition`：条件基类（抽象）
- `BaseRequirement`：领取条件基类
- `BaseSatisfy`：使用条件基类
- 具体条件实体：如 `RegisterDaysRequirement`、`OrderAmountSatisfy`

### 服务组件

- `ConditionHandlerFactory`：条件处理器工厂
- `ConditionManagerService`：条件管理服务

## 内置条件类型

### 领取条件

#### 注册天数限制 (register_days)
限制用户注册天数范围内才能领取优惠券。

**配置字段：**
- `minDays`：最少注册天数（必填）
- `maxDays`：最多注册天数（可选）

#### VIP等级限制 (vip_level)
限制用户VIP等级范围内才能领取优惠券。

**配置字段：**
- `minLevel`：最低VIP等级（必填）
- `maxLevel`：最高VIP等级（可选）
- `allowedLevels`：允许的VIP等级列表（可选）

### 使用条件

#### 订单金额限制 (order_amount)
限制订单金额范围和商品分类才能使用优惠券。

**配置字段：**
- `minAmount`：最低订单金额（必填）
- `maxAmount`：最高订单金额（可选）
- `includeCategories`：包含商品分类（可选）
- `excludeCategories`：排除商品分类（可选）

## 使用方法

### 1. 创建条件

```php
use Tourze\CouponCoreBundle\Service\ConditionManagerService;

// 注入服务
$conditionManager = $this->get(ConditionManagerService::class);

// 创建注册天数条件
$condition = $conditionManager->createCondition($coupon, 'register_days', [
    'minDays' => 7,
    'maxDays' => 30,
]);
```

### 2. 验证条件

```php
// 验证领取条件
$canReceive = $conditionManager->checkRequirements($coupon, $user);

// 验证使用条件
$orderContext = new OrderContext('100.00', [], [1, 2, 3]);
$canUse = $conditionManager->checkSatisfies($coupon, $orderContext);
```

### 3. 获取可用条件类型

```php
use Tourze\CouponCoreBundle\Enum\ConditionScenario;

// 获取领取条件类型
$requirementTypes = $conditionManager->getAvailableConditionTypes(ConditionScenario::REQUIREMENT);

// 获取使用条件类型
$satisfyTypes = $conditionManager->getAvailableConditionTypes(ConditionScenario::SATISFY);
```

## 扩展新条件

### 1. 创建条件实体

```php
<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\CouponCoreBundle\Entity\BaseRequirement;

#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_custom')]
class CustomRequirement extends BaseRequirement
{
    #[ORM\Column(type: Types::STRING)]
    private string $customField;

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'label' => $this->getLabel(),
            'customField' => $this->customField,
            'enabled' => $this->isEnabled(),
            'remark' => $this->getRemark(),
        ];
    }

    // getter/setter 方法...
}
```

### 2. 创建条件处理器

```php
<?php

namespace App\Handler;

use Tourze\CouponCoreBundle\Interface\RequirementHandlerInterface;
use Tourze\CouponCoreBundle\Enum\ConditionScenario;
use Tourze\CouponCoreBundle\ValueObject\FormFieldFactory;
// ... 其他必要的 use 语句

class CustomRequirementHandler implements RequirementHandlerInterface
{
    public function getType(): string
    {
        return 'custom';
    }

    public function getLabel(): string
    {
        return '自定义条件';
    }

    public function getDescription(): string
    {
        return '这是一个自定义条件示例';
    }

    public function getFormFields(): iterable
    {
        yield FormFieldFactory::text('customField', '自定义字段')
            ->required()
            ->help('请输入自定义值');
            
        yield FormFieldFactory::integer('threshold', '阈值')
            ->min(0)
            ->max(100)
            ->help('设置判断阈值');
    }

    // 实现其他必要方法...
}
```

### 3. 表单字段API

系统提供了丰富的表单字段类型和流式API：

```php
// 基础字段类型
FormFieldFactory::text('name', '名称')
FormFieldFactory::integer('count', '数量')
FormFieldFactory::decimal('amount', '金额', 2)  // 2位小数
FormFieldFactory::boolean('enabled', '启用')
FormFieldFactory::choice('type', '类型', ['a' => 'A', 'b' => 'B'])
FormFieldFactory::array('items', '项目列表')

// 链式配置
FormFieldFactory::integer('age', '年龄')
    ->required()
    ->min(18)
    ->max(99)
    ->help('请输入年龄');

// 复杂配置
FormFieldFactory::choice('category', '分类')
    ->choices(['tech' => '技术', 'life' => '生活'])
    ->required()
    ->help('选择文章分类');
```

### 3. 注册处理器

处理器会通过 `coupon.condition_handler` 标签自动注册，无需手动配置。

## API接口

### 获取条件类型列表
```
GET /admin/condition/types/{scenario}
```

### 获取表单字段配置
```
GET /admin/condition/form-fields/{type}
```

### 验证条件配置
```
POST /admin/condition/validate
```

### 条件CRUD操作
```
POST /api/coupon/condition              # 创建条件
PUT /api/coupon/condition/{id}          # 更新条件
DELETE /api/coupon/condition/{id}       # 删除条件
GET /api/coupon/condition/coupon/{id}   # 获取优惠券条件列表
```

## 后台管理

系统提供了完整的后台管理界面：

1. **条件管理页面**：`/admin/condition/manage`
2. **动态表单生成**：根据条件类型自动生成对应表单
3. **实时验证**：配置保存前进行验证
4. **可视化展示**：条件列表和详情展示

## 数据库设计

系统使用 JOINED 继承策略：

- `coupon_condition_base`：基础条件表
- `coupon_requirement_base`：领取条件基表
- `coupon_satisfy_base`：使用条件基表
- `coupon_requirement_*`：具体领取条件表
- `coupon_satisfy_*`：具体使用条件表

## 最佳实践

1. **条件命名**：使用描述性的类型名称，如 `register_days`、`vip_level`
2. **配置验证**：在处理器中实现完整的配置验证逻辑
3. **错误处理**：使用专门的异常类提供清晰的错误信息
4. **性能优化**：合理使用数据库索引和查询优化
5. **扩展性**：设计时考虑未来可能的扩展需求

## 迁移指南

从旧的枚举系统迁移到新系统：

1. 保留现有的 `Requirement` 和 `Satisfy` 实体作为兼容层
2. 逐步将业务逻辑迁移到新的条件处理器
3. 新功能直接使用新的条件系统
4. 最终废弃旧系统（可选）
