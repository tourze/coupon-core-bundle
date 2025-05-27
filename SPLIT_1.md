# 条件系统拆分重构计划 (SPLIT_1)

## 概述

将 `coupon-core-bundle` 中的条件系统拆分为独立的 `condition-system-bundle`，实现真正通用的条件管理系统。

## 拆分目标

### 问题分析

当前条件系统存在明显的业务痕迹：

1. `ConditionScenario::REQUIREMENT/SATISFY` - 优惠券特定的领取/使用概念
2. `RequirementHandlerInterface/SatisfyHandlerInterface` - 业务特定接口
3. `createCondition(Coupon $coupon, ...)` - 直接依赖 Coupon 实体
4. `checkRequirement/checkSatisfy` - 业务特定方法名

### 拆分目标

创建完全通用的条件系统，可被任何需要条件管理的系统复用：

- 权限系统
- 工作流系统
- 规则引擎
- 活动系统
- 等等...

## 新的通用设计

### 1. 通用条件触发器

```php
// tourze/condition-system-bundle/src/Enum/ConditionTrigger.php
enum ConditionTrigger: string
{
    case BEFORE_ACTION = 'before_action';    // 动作前置条件
    case AFTER_ACTION = 'after_action';      // 动作后置条件  
    case DURING_ACTION = 'during_action';    // 动作中条件
    case VALIDATION = 'validation';          // 验证条件
    case FILTER = 'filter';                  // 过滤条件
}
```

### 2. 通用接口设计

```php
// 主体接口 - 替代 Coupon
interface SubjectInterface
{
    public function getSubjectId(): string;
    public function getSubjectType(): string;
}

// 执行者接口 - 替代 UserInterface  
interface ActorInterface
{
    public function getActorId(): string;
    public function getActorType(): string;
    public function getActorData(): array;
}

// 条件接口
interface ConditionInterface
{
    public function getId(): ?int;
    public function getSubject(): ?SubjectInterface;
    public function getType(): string;
    public function getLabel(): string;
    public function isEnabled(): bool;
    public function getTrigger(): ConditionTrigger;
    public function toArray(): array;
}
```

### 3. 通用处理器接口

```php
interface ConditionHandlerInterface
{
    public function getType(): string;
    public function getLabel(): string;
    public function getDescription(): string;
    public function getFormFields(): iterable;
    public function validateConfig(array $config): ValidationResult;
    public function createCondition(SubjectInterface $subject, array $config): ConditionInterface;
    public function updateCondition(ConditionInterface $condition, array $config): void;
    public function evaluate(ConditionInterface $condition, EvaluationContext $context): EvaluationResult;
    public function getDisplayText(ConditionInterface $condition): string;
    public function getSupportedTriggers(): array;
}
```

### 4. 通用评估上下文

```php
class EvaluationContext
{
    private function __construct(
        private readonly ActorInterface $actor,
        private readonly ?object $payload = null,
        private readonly array $metadata = []
    ) {}
    
    public static function create(ActorInterface $actor): self;
    public function withPayload(object $payload): self;
    public function withMetadata(array $metadata): self;
}

class EvaluationResult
{
    public static function pass(array $metadata = []): self;
    public static function fail(array $messages, array $metadata = []): self;
    public function isPassed(): bool;
    public function getMessages(): array;
}
```

## 拆分计划

### 阶段1: 创建通用条件系统 (1-2周)

#### 1.1 创建基础结构

- [x] 创建 `packages/condition-system-bundle` 目录
- [x] 创建基础文件结构
- [x] 配置 composer.json
- [x] 创建 Bundle 类

#### 1.2 实现核心接口和枚举

- [x] `src/Enum/ConditionTrigger.php` - 通用触发器
- [x] `src/Interface/SubjectInterface.php` - 主体接口
- [x] `src/Interface/ActorInterface.php` - 执行者接口
- [x] `src/Interface/ConditionInterface.php` - 条件接口
- [x] `src/Interface/ConditionHandlerInterface.php` - 处理器接口

#### 1.3 实现核心值对象

- [x] `src/ValueObject/EvaluationContext.php` - 评估上下文
- [x] `src/ValueObject/EvaluationResult.php` - 评估结果
- [x] `src/ValueObject/FormField.php` - 表单字段（迁移）
- [x] `src/ValueObject/FormFieldFactory.php` - 表单字段工厂（迁移）
- [x] `src/ValueObject/ValidationResult.php` - 验证结果（迁移）

#### 1.4 实现基础实体

- [x] `src/Entity/BaseCondition.php` - 基础条件实体
- [x] `src/Handler/AbstractConditionHandler.php` - 抽象处理器

#### 1.5 实现核心服务

- [x] `src/Service/ConditionHandlerFactory.php` - 处理器工厂
- [x] `src/Service/ConditionManagerService.php` - 条件管理服务

#### 1.6 实现异常类

- [x] `src/Exception/ConditionSystemException.php` - 基础异常
- [x] `src/Exception/ConditionHandlerNotFoundException.php` - 处理器未找到
- [x] `src/Exception/InvalidConditionConfigException.php` - 无效配置

### 阶段2: 创建优惠券适配层 (1周)

#### 2.1 在 coupon-core-bundle 中创建适配器

- [x] `src/Adapter/CouponSubject.php` - 优惠券主体适配器
- [x] `src/Adapter/UserActor.php` - 用户执行者适配器
- [x] `src/Enum/CouponConditionTrigger.php` - 业务触发器映射

#### 2.2 重构现有条件处理器 ✅

- [x] 重构 `RegisterDaysRequirementHandler` → `RegisterDaysConditionHandler`
- [x] 重构 `VipLevelRequirementHandler` → `VipLevelConditionHandler`
- [x] 重构 `GatherCountLimitRequirementHandler` → `GatherCountLimitConditionHandler`
- [x] 重构 `OrderAmountSatisfyHandler` → `OrderAmountConditionHandler`

#### 2.3 重构现有条件实体 ✅

- [x] 重构 `RegisterDaysRequirement` → `RegisterDaysCondition`
- [x] 重构 `VipLevelRequirement` → `VipLevelCondition`
- [x] 重构 `GatherCountLimitRequirement` → `GatherCountLimitCondition`
- [x] 重构 `OrderAmountSatisfy` → `OrderAmountCondition`

### 阶段3: 更新依赖和配置 (3-5天) ✅

#### 3.1 更新 composer.json ✅

- [x] condition-system-bundle 的 composer.json
- [x] coupon-core-bundle 添加对 condition-system-bundle 的依赖

#### 3.2 更新实体规范 ✅

- [x] 更新所有条件实体符合 Symfony Entity 设计规范
- [x] 创建完整的 Repository 类
- [x] 添加字段注释和类型规范

#### 3.3 现代化配置 ✅

- [x] 使用 AutoconfigureTag 实现零配置
- [x] 删除手动配置文件
- [x] 实现自动服务注册

### 阶段4: 测试和验证 (1周) ✅

#### 4.1 集成测试环境 ✅

- [x] 创建 IntegrationTestKernel 测试内核
- [x] 配置测试数据库和实体映射
- [x] 添加 symfony/phpunit-bridge 依赖

#### 4.2 集成测试用例 ✅

- [x] 测试条件系统与优惠券系统的集成
- [x] 测试所有条件处理器的注册和工作
- [x] 测试适配器模式的正确性
- [x] 验证服务容器配置
- [ ] 验证新的通用接口可用

#### 4.3 文档更新

- [ ] 更新 README
- [ ] 创建迁移指南
- [ ] 更新API文档

## 拆分后的架构

```
tourze/condition-system-bundle (通用条件系统)
├── src/
│   ├── Enum/ConditionTrigger.php
│   ├── Interface/
│   │   ├── SubjectInterface.php
│   │   ├── ActorInterface.php
│   │   ├── ConditionInterface.php
│   │   └── ConditionHandlerInterface.php
│   ├── ValueObject/
│   │   ├── EvaluationContext.php
│   │   ├── EvaluationResult.php
│   │   ├── FormField.php
│   │   └── ValidationResult.php
│   ├── Entity/BaseCondition.php
│   ├── Service/
│   │   ├── ConditionHandlerFactory.php
│   │   └── ConditionManagerService.php
│   └── Exception/
└── tests/

tourze/coupon-core-bundle (业务核心)
├── src/
│   ├── Adapter/
│   │   ├── CouponSubject.php
│   │   └── UserActor.php
│   ├── Enum/CouponConditionTrigger.php
│   ├── Entity/ (重构后的条件实体)
│   ├── Handler/ (重构后的处理器)
│   └── ... (其他业务代码)
└── composer.json (依赖 condition-system-bundle)
```

## 收益评估

### 通用性收益

- ✅ 可用于任何需要条件管理的系统
- ✅ 完全抽象的设计，无业务痕迹
- ✅ 插件化架构，易于扩展

### 维护性收益

- ✅ 条件逻辑与业务逻辑完全分离
- ✅ 独立的测试和版本管理
- ✅ 清晰的职责边界

### 复用性收益

- ✅ 权限系统可复用
- ✅ 工作流系统可复用
- ✅ 规则引擎可复用
- ✅ 活动系统可复用

## 风险控制

### 向后兼容

- 保持现有API接口不变
- 通过适配器模式实现平滑过渡
- 分阶段迁移，确保每步都可回滚

### 测试保障

- 完整的单元测试覆盖
- 集成测试验证功能完整性
- 性能测试确保无性能退化

### 文档支持

- 详细的迁移指南
- 完整的API文档
- 示例代码和最佳实践

## 时间安排

- **第1-2周**: 阶段1 - 创建通用条件系统
- **第3周**: 阶段2 - 创建优惠券适配层
- **第4周**: 阶段3 - 更新依赖和配置
- **第5周**: 阶段4 - 测试和验证

**总计**: 5周完成拆分重构

## 成功标准

1. ✅ condition-system-bundle 完全独立，无业务依赖
2. ✅ 现有优惠券功能100%正常工作
3. ✅ 新的通用接口可以被其他系统复用
4. ✅ 测试覆盖率不低于90%
5. ✅ 性能无明显退化
6. ✅ 文档完整，易于理解和使用
