# CouponCoreBundle 开发文档

## 项目概述

CouponCoreBundle 是一个基于 Symfony 框架的企业级优惠券管理系统，提供了完整的优惠券生命周期管理功能，包括优惠券创建、发放、使用、核销等核心业务流程。

## 架构设计

### 1. 整体架构

```ascii
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Procedure     │    │     Service     │    │     Entity      │
│   (API 接口层)   │───▶│   (业务逻辑层)   │───▶│    (数据模型层)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Event       │    │     Message     │    │   Repository    │
│   (事件系统)     │    │   (异步消息)     │    │   (数据访问层)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 2. 核心实体模型

#### 主要实体关系

```ascii
Coupon (优惠券)
├── Code (券码) [1:N]
├── Category (分类) [N:1]
├── Channel (渠道) [N:N]
├── Satisfy (使用条件) [1:N]
├── Discount (抵扣配置) [1:N]
├── Requirement (领取条件) [1:N]
├── Attribute (属性) [1:N]
├── Batch (批次) [1:N]
└── WechatMiniProgramConfig (微信小程序配置) [1:1]
```

#### 核心实体说明

**1. Coupon（优惠券主体）**

- 支持多种券类型：满减券(DISCOUNT)、现金券(MONEY)、包邮券(FREIGHT)、口令券(COMMAND)等
- 包含有效期管理、库存控制、状态管理
- 支持 EasyAdmin 后台管理界面

**2. Code（券码实例）**

- 用户实际持有的券码，每个 Code 对应一个 Coupon
- 状态管理：未使用(UNUSED)、已使用(USED)、无效(INVALID)、已过期(EXPIRED)
- 支持激活机制和锁定机制
- 包含领取时间、过期时间、使用时间等时间戳

**3. Category（分类管理）**

- 树形结构的分类系统，支持无限层级
- 包含 LOGO、描述、显示标签等展示信息
- 支持有效期控制

**4. Channel（渠道管理）**

- 多平台券码分发支持
- 包含渠道编码、LOGO、跳转链接、小程序 AppID 等配置

### 3. 业务服务层

#### CouponService（核心服务）

```php
class CouponService
{
    // 券码生成与管理
    public function createOneCode(Coupon $coupon): Code;
    public function pickCode(UserInterface $user, Coupon $coupon, bool $renewable = true): ?Code;

    // 业务流程
    public function sendCode(UserInterface $user, Coupon $coupon, string $extend = ''): Code;
    public function redeemCode(Code $code, ?object $extra = null): void;

    // 库存管理
    public function getCouponValidStock(Coupon $coupon): int;

    // 规则验证
    public function checkCouponRequirement(UserInterface $user, Coupon $coupon): bool;
}
```

#### 主要功能模块

1. **券码生成**：使用 `atelierdisko/coupon_code` 生成唯一券码
2. **库存管理**：实时库存查询和事件驱动的库存更新
3. **规则引擎**：支持领取条件和使用条件的灵活配置
4. **状态管理**：完整的券码状态流转管理
5. **事件系统**：关键业务节点的事件触发机制

### 4. 接口层设计

#### JSON-RPC 接口结构

```ascii
Procedure/
├── Admin/           # 管理端接口
│   ├── Coupon/     # 优惠券管理
│   └── Category/   # 分类管理
├── Code/           # 券码相关接口
├── Coupon/         # 用户端优惠券接口
└── Category/       # 分类查询接口
```

#### 关键接口说明

1. **用户端接口**
   - `GatherCoupon`: 领取优惠券
   - `RedeemCoupon`: 使用优惠券
   - `GetUserCouponList`: 获取用户优惠券列表
   - `GetCouponDetail`: 获取优惠券详情

2. **管理端接口**
   - `AdminCreateCoupon`: 创建优惠券
   - `AdminGenerateCouponCode`: 批量生成券码
   - `AdminGetCouponList`: 优惠券列表管理

### 5. 事件系统

#### 事件驱动架构

```php
// 核心事件
- CodeRedeemEvent: 券码核销事件
- SendCodeEvent: 发送券码事件
- CountCouponValidStockEvent: 库存统计事件
- CouponDetailFormatEvent: 优惠券详情格式化事件
- BeforeGetUserCouponListEvent: 获取用户券列表前置事件
```

#### 事件使用场景

1. **库存同步**：通过 `CountCouponValidStockEvent` 支持外部系统库存同步
2. **业务扩展**：通过 `CouponDetailFormatEvent` 支持自定义格式化逻辑
3. **审计日志**：通过各类事件记录关键操作
4. **第三方集成**：通过事件系统集成外部系统

### 6. 命令行工具

```bash
# 定时任务
php bin/console coupon:check-expired-category    # 检查过期分类
php bin/console coupon:revoke-expired-code       # 回收过期券码
php bin/console coupon:send-plan                 # 发送优惠券计划
```

## 技术特性

### 1. 设计模式应用

- **Repository 模式**：数据访问层抽象
- **Service 层模式**：业务逻辑封装
- **Event Dispatcher 模式**：松耦合的事件驱动架构
- **Strategy 模式**：多种券类型和规则策略
- **Factory 模式**：券码生成工厂

### 2. 性能优化策略

- **延迟加载**：Entity 关联使用 `fetch: 'EXTRA_LAZY'`
- **数据库索引**：通过 `IndexColumn` 注解优化查询
- **缓存机制**：CacheableProcedure 支持接口级缓存
- **异步处理**：使用 Symfony Messenger 进行异步券码生成

### 3. 扩展性设计

- **Bundle 依赖管理**：通过 `BundleDependencyInterface` 管理依赖
- **契约接口**：通过 `tourze/coupon-contracts` 定义标准接口
- **事件钩子**：在关键业务节点提供事件扩展点
- **多态支持**：支持多种券类型和规则类型的扩展

## 当前实现状态

### ✅ 已完成功能

1. **核心实体模型**：完整的实体设计和关联关系
2. **基础业务流程**：券码生成、发放、使用、核销
3. **管理后台**：基于 EasyAdmin 的完整管理界面
4. **API 接口**：完整的 JSON-RPC 接口体系
5. **规则引擎**：基础的领取条件和使用条件支持
6. **事件系统**：关键业务节点的事件支持
7. **命令行工具**：定时任务和批量处理工具
8. **多渠道支持**：基础的渠道管理功能

### 🔄 测试覆盖情况

根据 `TEST_PLAN.md`，当前测试完成情况：

**已完成测试**：

- Entity 层：`CategoryTest`、`CodeTest`、`CouponTest`
- Repository 层：`CodeRepositoryTest`  
- Service 层：`CodeServiceTest`、`PlanServiceTest`、`CouponServiceTest`

**待完成测试**：

- 其他 Repository 层测试
- Procedure 层集成测试
- Event 层测试

## 多优惠券类型架构设计

### 当前类型系统现状

目前 CouponCoreBundle 通过 `CouponType` 枚举支持多种券类型：

```php
enum CouponType: string 
{
    case DISCOUNT = 'DISCOUNT';     // 满减券
    case MONEY = 'MONEY';           // 现金券  
    case FREIGHT = 'FREIGHT';       // 包邮券
    case COMMAND = 'COMMAND';       // 口令券
    case WEAPP_LINK = 'weapp-link'; // 小程序外链
    case H5_LINK = 'h5-link';       // H5外链
    case THIRD_PARTY = 'third-party'; // 三方优惠券
}
```

### 设计挑战与问题

#### 1. 配置差异问题

不同类型优惠券的配置结构存在显著差异：

```php
// 满减券配置
class DiscountCouponConfig {
    private int $thresholdAmount;    // 门槛金额
    private int $discountAmount;     // 抵扣金额
    private array $categoryIds;      // 适用品类
}

// 包邮券配置  
class FreightCouponConfig {
    private int $freeShippingThreshold; // 包邮门槛
    private array $regionCodes;         // 适用地区
    private array $excludeProducts;     // 排除商品
}

// 现金券配置
class MoneyCouponConfig {
    private int $faceValue;          // 面额
    private int $minOrderAmount;     // 最小订单金额
}
```

#### 2. 业务逻辑差异

每种券类型的核心业务逻辑完全不同：

- **满减券**：需要计算订单是否满足门槛条件，计算具体抵扣金额
- **包邮券**：需要判断配送地址，计算运费减免
- **现金券**：直接抵扣订单总额
- **口令券**：需要验证口令有效性

#### 3. 验证规则差异

不同类型的验证逻辑差异很大：

```php
// 当前统一处理方式存在的问题
class CouponService
{
    public function validateCoupon(Code $code, OrderContext $context): bool 
    {
        // 所有类型用同一套验证逻辑，无法很好处理类型差异
        switch ($code->getCoupon()->getType()) {
            case CouponType::DISCOUNT:
                // 满减券验证逻辑
            case CouponType::FREIGHT:
                // 包邮券验证逻辑
            // ... 随着类型增加，这里会变得很臃肿
        }
    }
}
```

### 推荐架构改造方案

#### 1. 策略模式 + 工厂模式设计

##### 核心接口定义

```php
interface CouponTypeHandlerInterface
{
    /**
     * 获取支持的券类型
     */
    public function getSupportedType(): CouponType;

    /**
     * 验证券码是否可用
     */
    public function validateCode(Code $code, UsageContext $context): ValidationResult;

    /**
     * 计算优惠金额
     */
    public function calculateDiscount(Code $code, OrderContext $context): DiscountResult;

    /**
     * 应用优惠到订单
     */
    public function applyDiscount(Code $code, Order $order): ApplyResult;

    /**
     * 获取券码展示信息
     */
    public function formatCodeDisplay(Code $code): array;

    /**
     * 获取类型特定配置
     */
    public function getTypeConfig(): array;

    /**
     * 验证类型特定配置
     */
    public function validateConfig(array $config): ConfigValidationResult;
}
```

##### 具体类型处理器实现

```php
// 满减券处理器
class DiscountCouponHandler implements CouponTypeHandlerInterface
{
    public function getSupportedType(): CouponType
    {
        return CouponType::DISCOUNT;
    }

    public function validateCode(Code $code, UsageContext $context): ValidationResult
    {
        $config = $this->getDiscountConfig($code->getCoupon());

        // 检查订单金额是否达到门槛
        if ($context->getOrderAmount() < $config->getThresholdAmount()) {
            return ValidationResult::fail('订单金额未达到使用门槛');
        }

        // 检查商品品类是否符合
        if (!$this->checkCategoryMatch($context->getProducts(), $config->getCategoryIds())) {
            return ValidationResult::fail('订单商品不符合使用条件');
        }

        return ValidationResult::success();
    }

    public function calculateDiscount(Code $code, OrderContext $context): DiscountResult
    {
        $config = $this->getDiscountConfig($code->getCoupon());

        // 计算实际可抵扣金额
        $discountAmount = min(
            $config->getDiscountAmount(),
            $context->getOrderAmount() - 1 // 至少保留1分钱
        );

        return new DiscountResult(
            amount: $discountAmount,
            type: DiscountType::ORDER_AMOUNT,
            description: "满{$config->getThresholdAmount()}减{$config->getDiscountAmount()}"
        );
    }
    
    private function getDiscountConfig(Coupon $coupon): DiscountCouponConfig
    {
        // 从 Coupon 的配置字段或关联实体中获取满减券特定配置
        return DiscountCouponConfig::fromJson($coupon->getTypeConfig());
    }
}

// 包邮券处理器
class FreightCouponHandler implements CouponTypeHandlerInterface
{
    public function getSupportedType(): CouponType
    {
        return CouponType::FREIGHT;
    }
    
    public function validateCode(Code $code, UsageContext $context): ValidationResult
    {
        $config = $this->getFreightConfig($code->getCoupon());
        
        // 检查配送地址
        if (!$this->isRegionSupported($context->getShippingAddress(), $config)) {
            return ValidationResult::fail('当前配送地址不支持使用包邮券');
        }
        
        // 检查是否有运费
        if ($context->getShippingCost() <= 0) {
            return ValidationResult::fail('订单无运费，无需使用包邮券');
        }
        
        return ValidationResult::success();
    }
    
    public function calculateDiscount(Code $code, OrderContext $context): DiscountResult
    {
        return new DiscountResult(
            amount: $context->getShippingCost(),
            type: DiscountType::SHIPPING_COST,
            description: '包邮券抵扣运费'
        );
    }
}

// 现金券处理器
class MoneyCouponHandler implements CouponTypeHandlerInterface
{
    public function getSupportedType(): CouponType
    {
        return CouponType::MONEY;
    }
    
    public function calculateDiscount(Code $code, OrderContext $context): DiscountResult
    {
        $config = $this->getMoneyConfig($code->getCoupon());
        
        $discountAmount = min(
            $config->getFaceValue(),
            $context->getOrderAmount() - 1
        );
        
        return new DiscountResult(
            amount: $discountAmount,
            type: DiscountType::ORDER_AMOUNT,
            description: "现金券抵扣￥{$discountAmount}"
        );
    }
}
```

##### 工厂和注册器

```php
class CouponTypeHandlerFactory
{
    /**
     * @var array<string, CouponTypeHandlerInterface>
     */
    private array $handlers = [];
    
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }
    
    public function registerHandler(CouponTypeHandlerInterface $handler): void
    {
        $this->handlers[$handler->getSupportedType()->value] = $handler;
    }
    
    public function getHandler(CouponType $type): CouponTypeHandlerInterface
    {
        if (!isset($this->handlers[$type->value])) {
            throw new CouponTypeNotFoundException("未找到类型 {$type->value} 的处理器");
        }
        
        return $this->handlers[$type->value];
    }
    
    public function getAllSupportedTypes(): array
    {
        return array_keys($this->handlers);
    }
}
```

#### 2. 重构核心服务

##### 新的 CouponService 设计

```php
class CouponService
{
    public function __construct(
        private readonly CouponTypeHandlerFactory $handlerFactory,
        private readonly CouponRepository $couponRepository,
        private readonly CodeRepository $codeRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}
    
    public function validateCouponUsage(Code $code, UsageContext $context): ValidationResult
    {
        // 基础验证（通用逻辑）
        $basicValidation = $this->performBasicValidation($code, $context);
        if (!$basicValidation->isValid()) {
            return $basicValidation;
        }
        
        // 类型特定验证
        $handler = $this->handlerFactory->getHandler($code->getCoupon()->getType());
        return $handler->validateCode($code, $context);
    }
    
    public function calculateCouponDiscount(Code $code, OrderContext $context): DiscountResult
    {
        $handler = $this->handlerFactory->getHandler($code->getCoupon()->getType());
        return $handler->calculateDiscount($code, $context);
    }
    
    public function applyCouponToOrder(Code $code, Order $order): ApplyResult
    {
        $context = OrderContext::fromOrder($order);
        
        // 验证券码可用性
        $validation = $this->validateCouponUsage($code, $context->toUsageContext());
        if (!$validation->isValid()) {
            throw new CouponValidationException($validation->getErrorMessage());
        }
        
        // 计算优惠
        $discountResult = $this->calculateCouponDiscount($code, $context);
        
        // 应用优惠
        $handler = $this->handlerFactory->getHandler($code->getCoupon()->getType());
        $applyResult = $handler->applyDiscount($code, $order);
        
        // 记录使用
        $this->markCodeAsUsed($code, $order);
        
        // 触发事件
        $this->eventDispatcher->dispatch(new CouponAppliedEvent($code, $order, $applyResult));
        
        return $applyResult;
    }
    
    private function performBasicValidation(Code $code, UsageContext $context): ValidationResult
    {
        // 券码状态检查
        if ($code->getStatus() !== CodeStatus::UNUSED) {
            return ValidationResult::fail('券码不可用');
        }
        
        // 有效期检查
        if ($code->isExpired()) {
            return ValidationResult::fail('券码已过期');
        }
        
        // 用户权限检查
        if ($code->getOwner() !== $context->getUser()) {
            return ValidationResult::fail('无权使用此券码');
        }
        
        return ValidationResult::success();
    }
}
```

#### 3. 配置存储方案

##### 方案A：JSON配置字段

```php
// 在 Coupon 实体中添加类型特定配置
class Coupon 
{
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $typeConfig = null;
    
    public function getTypeConfig(): array 
    {
        return $this->typeConfig ?? [];
    }
    
    public function setTypeConfig(array $config): void
    {
        $this->typeConfig = $config;
    }
}
```

##### 方案B：继承配置实体

```php
// 基础配置实体
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'discount' => DiscountCouponConfig::class,
    'freight' => FreightCouponConfig::class,
    'money' => MoneyCouponConfig::class,
])]
abstract class CouponTypeConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\OneToOne(targetEntity: Coupon::class)]
    private ?Coupon $coupon = null;
    
    abstract public function validate(): array;
}

// 具体配置实现
#[ORM\Entity]
class DiscountCouponConfig extends CouponTypeConfig
{
    #[ORM\Column]
    private int $thresholdAmount;
    
    #[ORM\Column]  
    private int $discountAmount;
    
    #[ORM\Column(type: Types::JSON)]
    private array $categoryIds = [];
    
    public function validate(): array
    {
        $errors = [];
        
        if ($this->thresholdAmount <= 0) {
            $errors[] = '门槛金额必须大于0';
        }
        
        if ($this->discountAmount <= 0) {
            $errors[] = '抵扣金额必须大于0';
        }
        
        return $errors;
    }
}
```

#### 4. 服务注册配置

```yaml
# services.yaml
services:
    CouponCoreBundle\Handler\DiscountCouponHandler:
        tags: ['coupon.type_handler']
        
    CouponCoreBundle\Handler\FreightCouponHandler:
        tags: ['coupon.type_handler']
        
    CouponCoreBundle\Handler\MoneyCouponHandler:
        tags: ['coupon.type_handler']
        
    CouponCoreBundle\Service\CouponTypeHandlerFactory:
        arguments:
            $handlers: !tagged_iterator coupon.type_handler
```

#### 5. 管理后台适配

```php
// 动态表单字段生成
class CouponAdminController extends AbstractCrudController
{
    public function configureFields(string $pageName): iterable
    {
        $fields = [
            // 基础字段
            TextField::new('name'),
            ChoiceField::new('type')->setChoices($this->getCouponTypeChoices()),
        ];
        
        // 根据类型动态添加配置字段
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $fields[] = $this->createTypeConfigField();
        }
        
        return $fields;
    }
    
    private function createTypeConfigField(): FormField
    {
        return FormField::addPanel('类型配置')
            ->setTemplatePath('admin/coupon/type_config.html.twig');
    }
}
```

### 改造实施步骤

#### 阶段1：接口和基础设施（1-2周）

1. ✅ 定义 `CouponTypeHandlerInterface` 接口
2. ✅ 创建 `CouponTypeHandlerFactory` 工厂类
3. ✅ 定义相关的值对象（ValidationResult、DiscountResult等）
4. ✅ 更新 Coupon 实体支持类型配置存储

#### 阶段2：核心类型处理器实现（2-3周）

1. ✅ 实现 `DiscountCouponHandler`（满减券）
2. ✅ 实现 `MoneyCouponHandler`（现金券）
3. ✅ 实现 `FreightCouponHandler`（包邮券）
4. ✅ 为每个处理器编写完整单元测试

#### 阶段3：服务层重构（1-2周）

1. ✅ 重构 `CouponService` 使用新的处理器架构
2. ✅ 更新相关的 Procedure 接口
3. ✅ 确保向后兼容性

#### 阶段4：管理后台适配（1-2周）

1. ✅ 更新 EasyAdmin 配置支持动态类型配置
2. ✅ 创建类型特定的配置表单
3. ✅ 实现配置验证和预览功能

#### 阶段5：新类型扩展（按需）

1. ✅ 实现 `CommandCouponHandler`（口令券）
2. ✅ 实现 `ThirdPartyCouponHandler`（三方券）
3. ✅ 实现 `LinkCouponHandler`（外链券）

### 扩展性优势

#### 1. 新增券类型非常简单

```php
// 只需实现接口即可
class NewTypeCouponHandler implements CouponTypeHandlerInterface
{
    public function getSupportedType(): CouponType
    {
        return CouponType::NEW_TYPE;
    }
    
    // 实现其他必需方法...
}
```

#### 2. 类型间相互独立

- 每种类型的逻辑完全隔离
- 修改一种类型不影响其他类型
- 便于并行开发和测试

#### 3. 配置灵活性

- 每种类型可以有完全不同的配置结构
- 支持复杂的业务规则定制
- 易于扩展新的配置项

#### 4. 测试友好

- 每个处理器可以独立测试
- Mock 依赖简单
- 测试覆盖率容易达到

### 性能考虑

#### 1. 处理器缓存

```php
class CachedCouponTypeHandlerFactory extends CouponTypeHandlerFactory
{
    private array $handlerCache = [];
    
    public function getHandler(CouponType $type): CouponTypeHandlerInterface
    {
        if (!isset($this->handlerCache[$type->value])) {
            $this->handlerCache[$type->value] = parent::getHandler($type);
        }
        
        return $this->handlerCache[$type->value];
    }
}
```

#### 2. 配置缓存

```php
class CouponConfigCache
{
    public function getTypeConfig(Coupon $coupon): array
    {
        $cacheKey = "coupon:config:{$coupon->getId()}";
        
        return $this->cache->get($cacheKey, function() use ($coupon) {
            return $coupon->getTypeConfig();
        });
    }
}
```

## 下一步完善方向

### 🎯 短期目标（1-2个月）

#### 1. 测试覆盖率提升

```bash
# 目标：达到 90% 以上的测试覆盖率
├── Repository 层完整测试
├── Service 层边界条件测试  
├── Procedure 层集成测试
└── Event 系统测试
```

**实施方案**：

- 为每个 Repository 创建对应的测试类
- 增加 Service 层的异常场景测试
- 编写 Procedure 层的端到端测试
- 完善 Event 系统的单元测试

#### 2. 性能优化

```php
// 大批量券码生成优化
class CouponService
{
    // 优化：批量生成券码，减少数据库交互
    public function batchCreateCodes(Coupon $coupon, int $quantity): array;

    // 优化：券码查询缓存
    public function getCachedValidStock(Coupon $coupon): int;
}
```

**实施方案**：

- 实现批量券码生成 API
- 增加 Redis 缓存层
- 优化数据库查询和索引
- 实现分页和流式处理

#### 3. 文档完善

- API 接口文档自动生成
- 使用示例和最佳实践文档
- 部署和运维文档
- 性能调优指南

### 🚀 中期目标（3-6个月）

#### 1. 规则引擎增强

```php
// 新增：更灵活的规则配置
interface RuleEngineInterface
{
    public function evaluate(array $context, array $rules): bool;
}

class CouponRuleEngine implements RuleEngineInterface
{
    // 支持复杂的组合规则
    public function evaluate(array $context, array $rules): bool;
}
```

**功能增强**：

- 支持复杂的组合条件（AND、OR、NOT）
- 动态规则配置界面
- 规则模板和预设
- 规则验证和测试工具

#### 2. 数据分析模块

```php
class CouponAnalyticsService
{
    // 发放统计
    public function getIssuanceStats(AnalyticsFilter $filter): array;

    // 使用统计
    public function getUsageStats(AnalyticsFilter $filter): array;

    // 效果分析
    public function getEffectivenessStats(AnalyticsFilter $filter): array;
}
```

**功能包含**：

- 实时数据看板
- 券的转化率分析
- 用户行为分析
- 成本效益分析

#### 3. 高级功能扩展

**券码个性化**：

- 个性化券码生成
- 自定义券码前缀/后缀
- 券码美化显示

**营销功能**：

- 券码分享机制
- 邀请奖励券
- 节日主题券设计

### 🌟 长期目标（6个月以上）

#### 1. 分布式架构支持

```php
// 分布式券码生成
class DistributedCouponService
{
    public function generateDistributedCodes(Coupon $coupon, int $quantity): void;
}

// 分布式锁管理
class DistributedLockManager
{
    public function lockCode(Code $code): bool;
    public function unlockCode(Code $code): void;
}
```

**架构升级**：

- 支持多实例部署
- 分布式券码生成
- 分布式锁管理
- 数据分片策略

#### 2. 微服务化重构

```yaml
# 服务拆分方案
services:
  coupon-core:        # 核心优惠券服务
  coupon-generator:   # 券码生成服务
  coupon-analytics:   # 数据分析服务
  coupon-notification: # 通知服务
```

**服务划分**：

- 核心业务服务
- 券码生成服务
- 数据分析服务
- 通知推送服务

#### 3. AI 智能化功能

```php
class IntelligentCouponService
{
    // 智能推荐券类型
    public function recommendCouponType(UserInterface $user): array;

    // 预测券使用率
    public function predictUsageRate(Coupon $coupon): float;

    // 智能定价建议
    public function suggestOptimalDiscount(array $context): array;
}
```

**AI 功能**：

- 个性化券推荐
- 使用率预测
- 智能定价
- 反欺诈检测

## 技术债务和风险

### 当前技术债务

1. **测试覆盖率不足**：需要补充完整的单元测试和集成测试
2. **文档不完整**：缺少详细的 API 文档和使用指南
3. **性能瓶颈**：大批量券码生成时的性能问题
4. **硬编码配置**：部分业务逻辑硬编码，缺乏灵活性

### 潜在风险

1. **并发安全**：高并发场景下的券码库存管理
2. **数据一致性**：分布式环境下的数据一致性保证
3. **扩展性限制**：当前架构在超大规模场景下的扩展性
4. **安全风险**：券码伪造和重放攻击防护

### 风险缓解方案

1. **并发控制**：实现分布式锁和乐观锁机制
2. **数据同步**：建立完善的数据同步和校验机制
3. **架构重构**：逐步向微服务架构演进
4. **安全加固**：加强券码加密和验证机制

## 开发规范

### 代码规范

1. **命名规范**：遵循 PSR-1、PSR-4、PSR-12 规范
2. **注释规范**：使用中文注释，重要方法需要详细说明
3. **类型声明**：严格使用 PHP 8+ 类型声明
4. **错误处理**：统一的异常处理机制

### 提交规范

```bash
# 提交格式
<type>(<scope>): <subject>

# 示例
feat(coupon): 添加批量券码生成功能
fix(code): 修复券码过期检查逻辑
docs(api): 更新接口文档
test(service): 添加 CouponService 单元测试
```

### 测试规范

1. **单元测试**：每个方法都需要对应的单元测试
2. **集成测试**：重要业务流程需要端到端测试
3. **性能测试**：关键接口需要性能测试
4. **测试覆盖率**：目标 90% 以上覆盖率

## 部署和运维

### 环境要求

```yaml
# 生产环境要求
php: ">=8.1"
symfony: ">=6.4"
mysql: ">=8.0"
redis: ">=6.0"
elasticsearch: ">=7.0"  # 可选，用于日志分析
```

### 监控指标

1. **业务指标**：券码生成速度、使用率、过期率
2. **技术指标**：接口响应时间、错误率、吞吐量
3. **资源指标**：CPU、内存、磁盘、网络使用率

### 备份策略

1. **数据备份**：定期全量备份和增量备份
2. **配置备份**：配置文件版本管理
3. **恢复测试**：定期进行备份恢复测试

## 贡献指南

### 开发流程

1. Fork 项目到个人仓库
2. 创建功能分支：`git checkout -b feature/xxx`
3. 提交代码：遵循提交规范
4. 运行测试：确保所有测试通过
5. 提交 Pull Request

### Code Review 清单

- [ ] 代码符合项目规范
- [ ] 包含完整的单元测试
- [ ] 文档更新完整
- [ ] 没有引入安全风险
- [ ] 性能影响可接受

---

**最后更新时间**: $(date '+%Y-%m-%d')
**文档版本**: v1.0.0
**维护人员**: CouponCoreBundle 开发团队
