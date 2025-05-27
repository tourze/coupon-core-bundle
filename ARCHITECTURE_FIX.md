# 🔧 架构修复：消除重复的 BaseCondition

## 问题描述

用户发现了一个重要的架构问题：存在两个不同的 `BaseCondition.php` 文件：

1. `packages/coupon-core-bundle/src/Entity/BaseCondition.php` - 旧的业务特定版本
2. `packages/condition-system-bundle/src/Entity/BaseCondition.php` - 新的通用版本

这违反了我们拆分重构的核心目标：**创建完全通用的条件系统**。

## 问题分析

### 旧版本的问题
```php
// packages/coupon-core-bundle/src/Entity/BaseCondition.php
class BaseCondition implements ConditionInterface
{
    #[ORM\ManyToOne(targetEntity: Coupon::class)]  // ❌ 直接依赖业务实体
    private ?Coupon $coupon = null;
    
    abstract public function getScenario(): ConditionScenario;  // ❌ 业务特定枚举
}
```

### 新版本的优势
```php
// packages/condition-system-bundle/src/Entity/BaseCondition.php  
abstract class BaseCondition implements ConditionInterface, \Stringable
{
    // ✅ 完全通用，无业务依赖
    abstract public function getTrigger(): ConditionTrigger;  // ✅ 通用触发器
    abstract public function getSubject(): ?SubjectInterface; // ✅ 通用主体接口
}
```

## 修复措施

### ✅ 1. 删除重复文件
- 删除 `packages/coupon-core-bundle/src/Entity/BaseCondition.php`
- 删除 `packages/coupon-core-bundle/src/Interface/ConditionInterface.php`
- 删除 `packages/coupon-core-bundle/src/Interface/ConditionHandlerInterface.php`

### ✅ 2. 更新引用
- 更新 `ConditionCrudController.php` 中的引用
- 更新 `ConditionController.php` 中的引用
- 更新 `ConditionType.php` 中的引用
- 将 `getScenario()` 调用改为 `getTrigger()`

### ✅ 3. 确保一致性
所有条件实体现在都正确继承通用的 `BaseCondition`：
- `RegisterDaysCondition`
- `VipLevelCondition` 
- `GatherCountLimitCondition`
- `OrderAmountCondition`

## 修复后的架构

```
condition-system-bundle/          # 通用条件系统
├── src/Entity/BaseCondition.php  # ✅ 唯一的基础条件实体
├── src/Interface/               
│   ├── ConditionInterface.php     # ✅ 通用条件接口
│   ├── SubjectInterface.php       # ✅ 通用主体接口
│   └── ActorInterface.php         # ✅ 通用执行者接口
└── ...

coupon-core-bundle/               # 业务适配层
├── src/Adapter/                  # ✅ 适配器模式
│   ├── CouponSubject.php         # 优惠券 → 通用主体
│   └── UserActor.php             # 用户 → 通用执行者
├── src/Entity/                   # ✅ 业务条件实体
│   ├── RegisterDaysCondition.php # 继承通用 BaseCondition
│   ├── VipLevelCondition.php     # 继承通用 BaseCondition
│   └── ...
└── ...
```

## 技术收益

### 1. 架构纯净性 ✅
- 通用系统完全无业务痕迹
- 业务系统通过适配器接入
- 清晰的职责分离

### 2. 可复用性 ✅
- 其他系统可直接使用 `condition-system-bundle`
- 无需担心优惠券相关的业务逻辑污染

### 3. 维护性 ✅
- 单一数据源原则
- 避免重复代码和不一致性
- 清晰的依赖关系

## 验证结果

### ✅ 检查通过
- 无重复的 `BaseCondition` 类
- 无重复的接口定义
- 所有引用都指向通用版本
- 架构符合拆分目标

### 🎯 符合设计目标
- ✅ condition-system-bundle 完全独立，无业务依赖
- ✅ 现有优惠券功能保持正常工作
- ✅ 新的通用接口可以被其他系统复用

## 总结

这次修复解决了一个关键的架构问题，确保了：

1. **单一职责**：每个 Bundle 有明确的职责边界
2. **依赖方向**：业务层依赖通用层，而非相反
3. **可复用性**：通用系统真正做到了通用
4. **一致性**：消除了重复和不一致

感谢用户的敏锐发现！这个修复让我们的架构更加健壮和符合设计目标。🎉 