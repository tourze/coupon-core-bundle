# 新条件系统使用示例

## 概述

经过重构，优惠券条件系统现在基于通用的 `condition-system-bundle`，提供了更好的扩展性和复用性。

## 核心特性

### 🚀 自动配置
使用 `AutoconfigureTag` 属性，处理器会自动注册，无需手动配置服务文件。

### 🔧 强类型设计
使用 PHP 8 枚举、属性和类型提示，提供更好的开发体验。

### 🔄 适配器模式
完美解耦业务逻辑与通用逻辑。

## 基本概念

### 触发器映射

| 业务概念 | 通用触发器 | 说明 |
|---------|-----------|------|
| 领取条件 (REQUIREMENT) | BEFORE_ACTION | 在执行动作前检查的条件 |
| 使用条件 (SATISFY) | VALIDATION | 验证时检查的条件 |

### 适配器

- `CouponSubject`: 将 Coupon 实体适配为通用的 SubjectInterface
- `UserActor`: 将 UserInterface 适配为通用的 ActorInterface

## 使用示例

### 1. 创建条件

```php
use Tourze\ConditionSystemBundle\Service\ConditionManagerService;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;

// 注入条件管理服务
/** @var ConditionManagerService $conditionManager */
$conditionManager = $container->get(ConditionManagerService::class);

// 创建优惠券主体适配器
$couponSubject = new CouponSubject($coupon);

// 创建注册天数条件
$registerDaysCondition = $conditionManager->createCondition(
    $couponSubject,
    'register_days',
    [
        'minDays' => 7,
        'maxDays' => 30,
    ]
);

// 创建VIP等级条件
$vipLevelCondition = $conditionManager->createCondition(
    $couponSubject,
    'vip_level',
    [
        'minLevel' => 2,
        'maxLevel' => 5,
    ]
);
```

### 2. 评估条件

```php
use Tourze\ConditionSystemBundle\ValueObject\EvaluationContext;
use Tourze\CouponCoreBundle\Adapter\UserActor;

// 创建用户执行者适配器
$userActor = new UserActor($user);

// 创建评估上下文
$context = EvaluationContext::create($userActor);

// 评估单个条件
$result = $conditionManager->evaluateCondition($registerDaysCondition, $context);

if ($result->isPassed()) {
    echo "条件通过";
} else {
    echo "条件失败: " . implode(', ', $result->getMessages());
}

// 批量评估条件
$conditions = [$registerDaysCondition, $vipLevelCondition];
$batchResult = $conditionManager->evaluateConditions($conditions, $context);
```

### 3. 获取可用条件类型

```php
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;

// 获取所有可用条件类型
$allTypes = $conditionManager->getAvailableConditionTypes();

// 获取支持特定触发器的条件类型
$beforeActionTypes = $conditionManager->getAvailableConditionTypes(
    ConditionTrigger::BEFORE_ACTION
);

foreach ($beforeActionTypes as $type => $info) {
    echo "类型: {$info['type']}, 标签: {$info['label']}";
}
```

## 扩展新的条件类型

### 1. 创建条件实体

```php
use Tourze\ConditionSystemBundle\Entity\BaseCondition;
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;

class CustomCondition extends BaseCondition
{
    private string $customField;

    public function getTrigger(): ConditionTrigger
    {
        return ConditionTrigger::BEFORE_ACTION;
    }

    public function getSubject(): ?SubjectInterface
    {
        return new CouponSubject($this->coupon);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'customField' => $this->customField,
            // ... 其他字段
        ];
    }

    // getter/setter 方法...
}
```

### 2. 创建条件处理器

```php
use Tourze\ConditionSystemBundle\Handler\AbstractConditionHandler;

// 🚀 自动注册！无需手动配置
class CustomConditionHandler extends AbstractConditionHandler
{
    public function getType(): string
    {
        return 'custom_condition';
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
    }

    public function validateConfig(array $config): ValidationResult
    {
        $errors = [];
        
        if (empty($config['customField'])) {
            $errors[] = '自定义字段不能为空';
        }
        
        return empty($errors) 
            ? ValidationResult::success() 
            : ValidationResult::failure($errors);
    }

    public function createCondition(SubjectInterface $subject, array $config): ConditionInterface
    {
        $condition = new CustomCondition();
        $condition->setCoupon($subject->getCoupon());
        $condition->setType($this->getType());
        $condition->setLabel($this->getLabel());
        $condition->setCustomField($config['customField']);
        
        return $condition;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof CustomCondition) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }
        
        $condition->setCustomField($config['customField']);
    }

    protected function doEvaluate(ConditionInterface $condition, EvaluationContext $context): EvaluationResult
    {
        if (!$condition instanceof CustomCondition) {
            return EvaluationResult::fail(['条件类型不匹配']);
        }

        // 实现自定义评估逻辑
        $customValue = $condition->getCustomField();
        
        // 示例：检查某个条件
        if ($customValue === 'valid') {
            return EvaluationResult::pass(['custom_check' => true]);
        }
        
        return EvaluationResult::fail(['自定义条件不满足']);
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof CustomCondition) {
            return '';
        }
        
        return "自定义条件: {$condition->getCustomField()}";
    }

    public function getSupportedTriggers(): array
    {
        return [ConditionTrigger::BEFORE_ACTION];
    }
}
```

### 3. 自动注册 🎉

由于使用了 `#[AutoconfigureTag('condition_system.handler')]`，处理器会自动注册！

```php
// ✅ 只需实现接口，无需任何配置
class MyConditionHandler extends AbstractConditionHandler
{
    // 实现方法即可自动注册
}
```

如果需要特殊配置，也可以手动配置：

```yaml
# config/services.yaml (可选)
services:
  App\Handler\MyConditionHandler:
    # 自定义配置
    arguments:
      $someService: '@some.service'
```

## 架构优势

### 🚀 开发效率
- **零配置**: 实现接口即自动注册
- **强类型**: IDE 智能提示和类型检查
- **热插拔**: 新增条件类型无需修改现有代码

### 🔧 维护性
- **职责分离**: 通用逻辑与业务逻辑完全分离
- **接口统一**: 所有条件处理器使用相同接口
- **测试友好**: 每个组件都可独立测试

### 🔄 扩展性
- **插件化**: 新的条件类型就是新的插件
- **复用性**: 其他系统可直接复用条件框架
- **向后兼容**: 现有代码无需修改

### 📊 性能
- **延迟加载**: 只有需要时才实例化处理器
- **缓存友好**: 处理器注册信息可缓存
- **批量处理**: 支持批量评估多个条件

## 迁移指南

### 从旧系统迁移

1. **处理器迁移**:
   ```php
   // 旧方式
   class OldHandler implements RequirementHandlerInterface
   {
       public function checkRequirement(...): bool
       {
           // 抛出异常表示失败
           throw new Exception('条件不满足');
       }
   }
   
   // 新方式
   class NewHandler extends AbstractConditionHandler
   {
       protected function doEvaluate(...): EvaluationResult
       {
           // 返回结果对象
           return EvaluationResult::fail(['条件不满足']);
       }
   }
   ```

2. **实体迁移**:
   ```php
   // 旧方式
   class OldCondition extends BaseRequirement
   {
       public function getScenario(): ConditionScenario
       {
           return ConditionScenario::REQUIREMENT;
       }
   }
   
   // 新方式
   class NewCondition extends BaseCondition
   {
       public function getTrigger(): ConditionTrigger
       {
           return ConditionTrigger::BEFORE_ACTION;
       }
   }
   ```

3. **服务使用**:
   ```php
   // 旧方式
   $handler = $handlerFactory->getHandler('type');
   $result = $handler->checkRequirement($condition, $user, $coupon);
   
   // 新方式
   $context = EvaluationContext::create(new UserActor($user));
   $result = $conditionManager->evaluateCondition($condition, $context);
   ```

## 最佳实践

### 1. 条件命名
- 使用描述性的类型名：`user_vip_level` 而不是 `vip`
- 保持一致的命名风格：`snake_case`

### 2. 错误处理
- 使用 `EvaluationResult::fail()` 返回具体错误信息
- 避免抛出异常，除非是系统级错误

### 3. 性能优化
- 在 `doEvaluate()` 中避免重复计算
- 使用缓存存储昂贵的计算结果

### 4. 测试策略
- 为每个处理器编写单元测试
- 测试各种边界条件和异常情况
- 使用模拟对象隔离依赖

## 总结

新的条件系统通过现代化的设计模式和 PHP 8 特性，提供了：

- 🚀 **零配置开发体验**
- 🔧 **强类型安全保障**  
- 🔄 **完美的扩展能力**
- 📊 **优秀的性能表现**

这为构建复杂的业务规则系统奠定了坚实的基础。 