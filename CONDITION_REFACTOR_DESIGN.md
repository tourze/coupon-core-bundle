# CouponCoreBundle 条件系统重构设计文档

## 当前问题分析

### 现有设计的局限性

当前的 `Requirement`（领取条件）和 `Satisfy`（使用条件）系统存在以下问题：

#### 1. 扩展性差

- 条件类型通过枚举固定定义，新增类型需要修改多处代码
- 验证逻辑硬编码在 `CouponService` 中，难以维护和扩展

#### 2. 数据存储不清晰

- 所有条件值都存储在单一的 `value` 字段（字符串类型）
- 复杂条件需要将多个参数编码到一个字符串中
- 缺乏类型安全和数据完整性保证

#### 3. 验证逻辑分散

- 不同条件的验证逻辑混在一起
- 难以进行单元测试和独立维护
- 业务逻辑和数据访问逻辑耦合严重

#### 4. 前端集成困难

- 后台管理界面无法根据条件类型动态生成表单
- 新增条件类型需要手动修改前端代码

## 改造目标

### 设计原则

1. **插件化架构**：通过实现服务接口来新增条件类型
2. **类型安全**：不同条件使用专门的数据实体，避免使用泛型字符串
3. **验证独立**：每种条件类型拥有独立的验证逻辑
4. **前端友好**：后台能自动识别并生成相应的表单组件
5. **向后兼容**：保持现有 API 接口的兼容性

### 核心要求

- 新增条件类型只需实现相应的服务和实体
- 后台管理界面自动发现新的条件类型
- 不同条件类型使用独立的数据表存储
- 支持复杂的条件配置和验证逻辑

## 架构设计

### 1. 核心接口设计

#### 条件处理器接口

```php
interface ConditionHandlerInterface
{
    /**
     * 获取条件类型标识符
     */
    public function getType(): string;

    /**
     * 获取条件类型显示名称
     */
    public function getLabel(): string;

    /**
     * 获取条件描述
     */
    public function getDescription(): string;

    /**
     * 获取表单字段配置
     */
    public function getFormFields(): array;

    /**
     * 验证条件配置的有效性
     */
    public function validateConfig(array $config): ValidationResult;

    /**
     * 创建条件实体
     */
    public function createCondition(Coupon $coupon, array $config): ConditionInterface;

    /**
     * 更新条件实体
     */
    public function updateCondition(ConditionInterface $condition, array $config): void;

    /**
     * 验证条件是否满足
     */
    public function validate(ConditionInterface $condition, ConditionContext $context): ValidationResult;

    /**
     * 获取条件的显示文本
     */
    public function getDisplayText(ConditionInterface $condition): string;

    /**
     * 获取支持的应用场景（领取/使用）
     */
    public function getSupportedScenarios(): array;
}
```

#### 领取条件处理器接口

```php
interface RequirementHandlerInterface extends ConditionHandlerInterface
{
    /**
     * 验证用户是否满足领取条件
     */
    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool;
}
```

#### 使用条件处理器接口

```php
interface SatisfyHandlerInterface extends ConditionHandlerInterface
{
    /**
     * 验证订单是否满足使用条件
     */
    public function checkSatisfy(SatisfyInterface $satisfy, OrderContext $orderContext): bool;
}
```

### 2. 实体设计

#### 基础条件实体

```php
#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'condition_type', type: 'string')]
abstract class BaseCondition implements ConditionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Coupon $coupon = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $type;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $label;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remark = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[CreateTimeColumn]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    private ?\DateTimeInterface $updateTime = null;

    // 抽象方法
    abstract public function getScenario(): ConditionScenario;
    abstract public function toArray(): array;
}
```

#### 领取条件基类

```php
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_base')]
abstract class BaseRequirement extends BaseCondition implements RequirementInterface
{
    public function getScenario(): ConditionScenario
    {
        return ConditionScenario::REQUIREMENT;
    }
}
```

#### 使用条件基类

```php
#[ORM\Entity]
#[ORM\Table(name: 'coupon_satisfy_base')]
abstract class BaseSatisfy extends BaseCondition implements SatisfyInterface
{
    public function getScenario(): ConditionScenario
    {
        return ConditionScenario::SATISFY;
    }
}
```

### 3. 具体条件实现示例

#### 注册天数限制条件

```php
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_register_days')]
class RegisterDaysRequirement extends BaseRequirement
{
    #[ORM\Column(type: Types::INTEGER)]
    private int $minDays = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxDays = null;

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'minDays' => $this->minDays,
            'maxDays' => $this->maxDays,
            'enabled' => $this->isEnabled(),
        ];
    }

    // getters and setters...
}

class RegisterDaysRequirementHandler implements RequirementHandlerInterface
{
    public function getType(): string
    {
        return 'register_days';
    }

    public function getLabel(): string
    {
        return '注册天数限制';
    }

    public function getDescription(): string
    {
        return '限制用户注册天数范围内才能领取优惠券';
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'minDays',
                'type' => 'integer',
                'label' => '最少注册天数',
                'required' => true,
                'min' => 0,
                'help' => '用户注册至少需要多少天才能领取',
            ],
            [
                'name' => 'maxDays',
                'type' => 'integer',
                'label' => '最多注册天数',
                'required' => false,
                'min' => 1,
                'help' => '用户注册不超过多少天才能领取，留空表示无上限',
            ],
        ];
    }

    public function validateConfig(array $config): ValidationResult
    {
        $errors = [];

        if (!isset($config['minDays']) || !is_int($config['minDays']) || $config['minDays'] < 0) {
            $errors[] = '最少注册天数必须是非负整数';
        }

        if (isset($config['maxDays']) && (!is_int($config['maxDays']) || $config['maxDays'] <= 0)) {
            $errors[] = '最多注册天数必须是正整数';
        }

        if (isset($config['minDays'], $config['maxDays']) && $config['minDays'] > $config['maxDays']) {
            $errors[] = '最少注册天数不能大于最多注册天数';
        }

        return empty($errors) ? ValidationResult::success() : ValidationResult::failure($errors);
    }

    public function createCondition(Coupon $coupon, array $config): ConditionInterface
    {
        $requirement = new RegisterDaysRequirement();
        $requirement->setCoupon($coupon);
        $requirement->setType($this->getType());
        $requirement->setLabel($this->getLabel());
        $requirement->setMinDays($config['minDays']);
        
        if (isset($config['maxDays'])) {
            $requirement->setMaxDays($config['maxDays']);
        }

        return $requirement;
    }

    public function updateCondition(ConditionInterface $condition, array $config): void
    {
        if (!$condition instanceof RegisterDaysRequirement) {
            throw new \InvalidArgumentException('条件类型不匹配');
        }

        $condition->setMinDays($config['minDays']);
        $condition->setMaxDays($config['maxDays'] ?? null);
    }

    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool
    {
        if (!$requirement instanceof RegisterDaysRequirement) {
            return false;
        }

        $registerDays = Carbon::now()->diff($user->getCreateTime())->days;

        if ($registerDays < $requirement->getMinDays()) {
            throw new CouponRequirementException("需要注册满{$requirement->getMinDays()}天才能领取");
        }

        if ($requirement->getMaxDays() && $registerDays > $requirement->getMaxDays()) {
            throw new CouponRequirementException("注册时间超过{$requirement->getMaxDays()}天无法领取");
        }

        return true;
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof RegisterDaysRequirement) {
            return '';
        }

        $text = "注册满{$condition->getMinDays()}天";
        if ($condition->getMaxDays()) {
            $text .= "且不超过{$condition->getMaxDays()}天";
        }

        return $text;
    }

    public function getSupportedScenarios(): array
    {
        return [ConditionScenario::REQUIREMENT];
    }

    public function validate(ConditionInterface $condition, ConditionContext $context): ValidationResult
    {
        return ValidationResult::success();
    }
}
```

#### 订单金额条件

```php
#[ORM\Entity]
#[ORM\Table(name: 'coupon_satisfy_order_amount')]
class OrderAmountSatisfy extends BaseSatisfy
{
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $minAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $maxAmount = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $excludeCategories = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $includeCategories = null;

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'minAmount' => $this->minAmount,
            'maxAmount' => $this->maxAmount,
            'excludeCategories' => $this->excludeCategories,
            'includeCategories' => $this->includeCategories,
            'enabled' => $this->isEnabled(),
        ];
    }

    // getters and setters...
}

class OrderAmountSatisfyHandler implements SatisfyHandlerInterface
{
    public function getType(): string
    {
        return 'order_amount';
    }

    public function getLabel(): string
    {
        return '订单金额限制';
    }

    public function getDescription(): string
    {
        return '订单金额需要满足指定条件才能使用优惠券';
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'minAmount',
                'type' => 'money',
                'label' => '最低订单金额',
                'required' => true,
                'min' => '0.01',
                'step' => '0.01',
                'help' => '订单至少需要多少金额才能使用',
            ],
            [
                'name' => 'maxAmount',
                'type' => 'money',
                'label' => '最高订单金额',
                'required' => false,
                'min' => '0.01',
                'step' => '0.01',
                'help' => '订单不超过多少金额才能使用，留空表示无上限',
            ],
            [
                'name' => 'includeCategories',
                'type' => 'choice',
                'label' => '适用品类',
                'multiple' => true,
                'required' => false,
                'choices' => '@category_choices',
                'help' => '只有包含这些品类的订单才能使用，留空表示所有品类',
            ],
            [
                'name' => 'excludeCategories',
                'type' => 'choice',
                'label' => '排除品类',
                'multiple' => true,
                'required' => false,
                'choices' => '@category_choices',
                'help' => '包含这些品类的订单不能使用优惠券',
            ],
        ];
    }

    public function checkSatisfy(SatisfyInterface $satisfy, OrderContext $orderContext): bool
    {
        if (!$satisfy instanceof OrderAmountSatisfy) {
            return false;
        }

        $orderAmount = $orderContext->getTotalAmount();

        // 检查最低金额
        if (bccomp($orderAmount, $satisfy->getMinAmount(), 2) < 0) {
            throw new CouponSatisfyException("订单金额需满{$satisfy->getMinAmount()}元");
        }

        // 检查最高金额
        if ($satisfy->getMaxAmount() && bccomp($orderAmount, $satisfy->getMaxAmount(), 2) > 0) {
            throw new CouponSatisfyException("订单金额不能超过{$satisfy->getMaxAmount()}元");
        }

        // 检查品类限制
        if ($satisfy->getIncludeCategories()) {
            if (!$orderContext->hasAnyCategory($satisfy->getIncludeCategories())) {
                throw new CouponSatisfyException('订单中没有适用的商品品类');
            }
        }

        if ($satisfy->getExcludeCategories()) {
            if ($orderContext->hasAnyCategory($satisfy->getExcludeCategories())) {
                throw new CouponSatisfyException('订单中包含不适用的商品品类');
            }
        }

        return true;
    }

    public function getDisplayText(ConditionInterface $condition): string
    {
        if (!$condition instanceof OrderAmountSatisfy) {
            return '';
        }

        $text = "订单满{$condition->getMinAmount()}元";
        if ($condition->getMaxAmount()) {
            $text .= "且不超过{$condition->getMaxAmount()}元";
        }

        return $text;
    }

    // 其他方法实现...
}
```

### 4. 工厂和注册系统

#### 条件处理器工厂

```php
class ConditionHandlerFactory
{
    private array $requirementHandlers = [];
    private array $satisfyHandlers = [];

    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function registerHandler(ConditionHandlerInterface $handler): void
    {
        $scenarios = $handler->getSupportedScenarios();

        foreach ($scenarios as $scenario) {
            if ($scenario === ConditionScenario::REQUIREMENT && $handler instanceof RequirementHandlerInterface) {
                $this->requirementHandlers[$handler->getType()] = $handler;
            } elseif ($scenario === ConditionScenario::SATISFY && $handler instanceof SatisfyHandlerInterface) {
                $this->satisfyHandlers[$handler->getType()] = $handler;
            }
        }
    }

    public function getRequirementHandler(string $type): RequirementHandlerInterface
    {
        if (!isset($this->requirementHandlers[$type])) {
            throw new ConditionHandlerNotFoundException("未找到领取条件处理器: {$type}");
        }

        return $this->requirementHandlers[$type];
    }

    public function getSatisfyHandler(string $type): SatisfyHandlerInterface
    {
        if (!isset($this->satisfyHandlers[$type])) {
            throw new ConditionHandlerNotFoundException("未找到使用条件处理器: {$type}");
        }

        return $this->satisfyHandlers[$type];
    }

    public function getAllRequirementHandlers(): array
    {
        return $this->requirementHandlers;
    }

    public function getAllSatisfyHandlers(): array
    {
        return $this->satisfyHandlers;
    }
}
```

#### 条件管理服务

```php
class ConditionManagerService
{
    public function __construct(
        private readonly ConditionHandlerFactory $handlerFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function createRequirement(Coupon $coupon, string $type, array $config): RequirementInterface
    {
        $handler = $this->handlerFactory->getRequirementHandler($type);
        
        $validation = $handler->validateConfig($config);
        if (!$validation->isValid()) {
            throw new InvalidConditionConfigException(implode('; ', $validation->getErrors()));
        }

        $requirement = $handler->createCondition($coupon, $config);
        
        $this->entityManager->persist($requirement);
        $this->entityManager->flush();

        return $requirement;
    }

    public function createSatisfy(Coupon $coupon, string $type, array $config): SatisfyInterface
    {
        $handler = $this->handlerFactory->getSatisfyHandler($type);
        
        $validation = $handler->validateConfig($config);
        if (!$validation->isValid()) {
            throw new InvalidConditionConfigException(implode('; ', $validation->getErrors()));
        }

        $satisfy = $handler->createCondition($coupon, $config);
        
        $this->entityManager->persist($satisfy);
        $this->entityManager->flush();

        return $satisfy;
    }

    public function updateRequirement(RequirementInterface $requirement, array $config): void
    {
        $handler = $this->handlerFactory->getRequirementHandler($requirement->getType());
        
        $validation = $handler->validateConfig($config);
        if (!$validation->isValid()) {
            throw new InvalidConditionConfigException(implode('; ', $validation->getErrors()));
        }

        $handler->updateCondition($requirement, $config);
        
        $this->entityManager->persist($requirement);
        $this->entityManager->flush();
    }

    public function checkAllRequirements(UserInterface $user, Coupon $coupon): bool
    {
        foreach ($coupon->getRequirements() as $requirement) {
            if (!$requirement->isEnabled()) {
                continue;
            }

            $handler = $this->handlerFactory->getRequirementHandler($requirement->getType());
            if (!$handler->checkRequirement($requirement, $user, $coupon)) {
                return false;
            }
        }

        return true;
    }

    public function checkAllSatisfies(OrderContext $orderContext, Coupon $coupon): bool
    {
        foreach ($coupon->getSatisfies() as $satisfy) {
            if (!$satisfy->isEnabled()) {
                continue;
            }

            $handler = $this->handlerFactory->getSatisfyHandler($satisfy->getType());
            if (!$handler->checkSatisfy($satisfy, $orderContext)) {
                return false;
            }
        }

        return true;
    }

    public function getAvailableRequirementTypes(): array
    {
        $types = [];
        foreach ($this->handlerFactory->getAllRequirementHandlers() as $type => $handler) {
            $types[$type] = [
                'type' => $type,
                'label' => $handler->getLabel(),
                'description' => $handler->getDescription(),
                'formFields' => $handler->getFormFields(),
            ];
        }

        return $types;
    }

    public function getAvailableSatisfyTypes(): array
    {
        $types = [];
        foreach ($this->handlerFactory->getAllSatisfyHandlers() as $type => $handler) {
            $types[$type] = [
                'type' => $type,
                'label' => $handler->getLabel(),
                'description' => $handler->getDescription(),
                'formFields' => $handler->getFormFields(),
            ];
        }

        return $types;
    }
}
```

### 5. 服务配置

```yaml
# services.yaml
services:
    # 条件处理器
    CouponCoreBundle\Handler\RegisterDaysRequirementHandler:
        tags: ['coupon.condition_handler']

    CouponCoreBundle\Handler\OrderAmountSatisfyHandler:
        tags: ['coupon.condition_handler']

    # 工厂服务
    CouponCoreBundle\Service\ConditionHandlerFactory:
        arguments:
            $handlers: !tagged_iterator coupon.condition_handler

    # 管理服务
    CouponCoreBundle\Service\ConditionManagerService:
        arguments:
            $handlerFactory: '@CouponCoreBundle\Service\ConditionHandlerFactory'
            $entityManager: '@doctrine.orm.entity_manager'
```

### 6. 重构现有的 CouponService

```php
class CouponService
{
    public function __construct(
        // 现有依赖...
        private readonly ConditionManagerService $conditionManager,
    ) {}

    public function checkCouponRequirement(UserInterface $user, Coupon $coupon): bool
    {
        return $this->conditionManager->checkAllRequirements($user, $coupon);
    }

    public function checkCouponSatisfy(OrderContext $orderContext, Coupon $coupon): bool
    {
        return $this->conditionManager->checkAllSatisfies($orderContext, $coupon);
    }

    // 保持向后兼容的方法...
}
```

## 详细实现方案

### 1. 核心值对象和枚举

```php
// 条件场景枚举
enum ConditionScenario: string
{
    case REQUIREMENT = 'requirement';  // 领取条件
    case SATISFY = 'satisfy';          // 使用条件
}

// 验证结果值对象
class ValidationResult
{
    private function __construct(
        private readonly bool $valid,
        private readonly array $errors = []
    ) {}

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(array $errors): self
    {
        return new self(false, $errors);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}

// 条件上下文
class ConditionContext
{
    private function __construct(
        private readonly UserInterface $user,
        private readonly ?object $data = null
    ) {}

    public static function forRequirement(UserInterface $user): self
    {
        return new self($user);
    }

    public static function forSatisfy(UserInterface $user, object $data): self
    {
        return new self($user, $data);
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getData(): ?object
    {
        return $this->data;
    }
}

// 订单上下文
class OrderContext
{
    public function __construct(
        private readonly string $totalAmount,
        private readonly array $items = [],
        private readonly array $categories = [],
        private readonly array $metadata = []
    ) {}

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function hasAnyCategory(array $categoryIds): bool
    {
        return !empty(array_intersect($this->categories, $categoryIds));
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
}
```

### 2. 核心接口定义

```php
// 条件接口
interface ConditionInterface
{
    public function getId(): ?int;
    public function getCoupon(): ?Coupon;
    public function getType(): string;
    public function getLabel(): string;
    public function isEnabled(): bool;
    public function getScenario(): ConditionScenario;
    public function toArray(): array;
}

// 领取条件接口
interface RequirementInterface extends ConditionInterface
{
    // 领取条件特有的方法
}

// 使用条件接口
interface SatisfyInterface extends ConditionInterface
{
    // 使用条件特有的方法
}
```

### 3. 数据库结构设计

#### 基础表结构

```sql
-- 条件基础表（使用 JOINED 继承）
CREATE TABLE coupon_condition_base (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    condition_type VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    remark TEXT,
    enabled BOOLEAN DEFAULT TRUE,
    create_time DATETIME,
    update_time DATETIME,
    FOREIGN KEY (coupon_id) REFERENCES coupon_main(id) ON DELETE CASCADE,
    INDEX idx_coupon_type (coupon_id, condition_type),
    INDEX idx_type (type)
);

-- 领取条件基础表
CREATE TABLE coupon_requirement_base (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES coupon_condition_base(id) ON DELETE CASCADE
);

-- 使用条件基础表
CREATE TABLE coupon_satisfy_base (
    id INT PRIMARY KEY,
    FOREIGN KEY (id) REFERENCES coupon_condition_base(id) ON DELETE CASCADE
);
```

#### 具体条件表

```sql
-- 注册天数条件表
CREATE TABLE coupon_requirement_register_days (
    id INT PRIMARY KEY,
    min_days INT NOT NULL DEFAULT 0,
    max_days INT NULL,
    FOREIGN KEY (id) REFERENCES coupon_requirement_base(id) ON DELETE CASCADE,
    CHECK (min_days >= 0),
    CHECK (max_days IS NULL OR max_days > 0),
    CHECK (max_days IS NULL OR max_days >= min_days)
);

-- 订单金额条件表
CREATE TABLE coupon_satisfy_order_amount (
    id INT PRIMARY KEY,
    min_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_amount DECIMAL(10,2) NULL,
    include_categories JSON NULL,
    exclude_categories JSON NULL,
    FOREIGN KEY (id) REFERENCES coupon_satisfy_base(id) ON DELETE CASCADE,
    CHECK (min_amount >= 0),
    CHECK (max_amount IS NULL OR max_amount > 0),
    CHECK (max_amount IS NULL OR max_amount >= min_amount)
);

-- VIP等级条件表
CREATE TABLE coupon_requirement_vip_level (
    id INT PRIMARY KEY,
    min_level INT NOT NULL DEFAULT 1,
    allowed_levels JSON NULL,
    FOREIGN KEY (id) REFERENCES coupon_requirement_base(id) ON DELETE CASCADE,
    CHECK (min_level >= 1)
);

-- 领取次数限制表
CREATE TABLE coupon_requirement_gather_limit (
    id INT PRIMARY KEY,
    max_count INT NOT NULL DEFAULT 1,
    time_period ENUM('TOTAL', 'DAILY', 'WEEKLY', 'MONTHLY') DEFAULT 'TOTAL',
    FOREIGN KEY (id) REFERENCES coupon_requirement_base(id) ON DELETE CASCADE,
    CHECK (max_count > 0)
);
```

### 4. API 接口设计

#### 条件管理 API

```php
#[MethodTag('优惠券模块')]
#[MethodDoc('获取可用的领取条件类型')]
#[MethodExpose('GetAvailableRequirementTypes')]
class GetAvailableRequirementTypesApi extends BaseProcedure
{
    public function __construct(
        private readonly ConditionManagerService $conditionManager
    ) {}

    public function execute(): array
    {
        return [
            'types' => $this->conditionManager->getAvailableRequirementTypes()
        ];
    }
}

#[MethodTag('优惠券模块')]
#[MethodDoc('创建领取条件')]
#[MethodExpose('CreateCouponRequirement')]
#[IsGranted('ROLE_ADMIN')]
class CreateCouponRequirementApi extends LockableProcedure
{
    public function __construct(
        private readonly ConditionManagerService $conditionManager,
        private readonly CouponRepository $couponRepository
    ) {}

    public function execute(): array
    {
        $couponId = $this->getRequest()->getParameter('couponId');
        $type = $this->getRequest()->getParameter('type');
        $config = $this->getRequest()->getParameter('config', []);

        $coupon = $this->couponRepository->find($couponId);
        if (!$coupon) {
            throw new CouponNotFoundException();
        }

        $requirement = $this->conditionManager->createRequirement($coupon, $type, $config);

        return [
            'requirement' => $requirement->toArray()
        ];
    }
}

#[MethodTag('优惠券模块')]
#[MethodDoc('更新领取条件')]
#[MethodExpose('UpdateCouponRequirement')]
#[IsGranted('ROLE_ADMIN')]
class UpdateCouponRequirementApi extends LockableProcedure
{
    public function __construct(
        private readonly ConditionManagerService $conditionManager,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function execute(): array
    {
        $requirementId = $this->getRequest()->getParameter('requirementId');
        $config = $this->getRequest()->getParameter('config', []);

        $requirement = $this->entityManager->getRepository(BaseRequirement::class)->find($requirementId);
        if (!$requirement) {
            throw new ConditionNotFoundException();
        }

        $this->conditionManager->updateRequirement($requirement, $config);

        return [
            'requirement' => $requirement->toArray()
        ];
    }
}
```

### 5. 后台管理界面集成

#### 动态表单生成器

```php
class ConditionFormBuilder
{
    public function __construct(
        private readonly ConditionHandlerFactory $handlerFactory
    ) {}

    public function buildRequirementForm(string $type): array
    {
        $handler = $this->handlerFactory->getRequirementHandler($type);
        $fields = $handler->getFormFields();

        return [
            'type' => $type,
            'label' => $handler->getLabel(),
            'description' => $handler->getDescription(),
            'fields' => $this->transformFields($fields)
        ];
    }

    public function buildSatisfyForm(string $type): array
    {
        $handler = $this->handlerFactory->getSatisfyHandler($type);
        $fields = $handler->getFormFields();

        return [
            'type' => $type,
            'label' => $handler->getLabel(),
            'description' => $handler->getDescription(),
            'fields' => $this->transformFields($fields)
        ];
    }

    private function transformFields(array $fields): array
    {
        $transformed = [];

        foreach ($fields as $field) {
            $transformedField = [
                'name' => $field['name'],
                'type' => $field['type'],
                'label' => $field['label'],
                'required' => $field['required'] ?? false,
                'help' => $field['help'] ?? null,
            ];

            // 处理不同字段类型的特殊属性
            switch ($field['type']) {
                case 'integer':
                    $transformedField['min'] = $field['min'] ?? null;
                    $transformedField['max'] = $field['max'] ?? null;
                    $transformedField['step'] = $field['step'] ?? 1;
                    break;

                case 'money':
                    $transformedField['min'] = $field['min'] ?? '0.01';
                    $transformedField['max'] = $field['max'] ?? null;
                    $transformedField['step'] = $field['step'] ?? '0.01';
                    $transformedField['currency'] = $field['currency'] ?? 'CNY';
                    break;

                case 'choice':
                    $transformedField['multiple'] = $field['multiple'] ?? false;
                    $transformedField['choices'] = $this->resolveChoices($field['choices']);
                    break;

                case 'date':
                case 'datetime':
                    $transformedField['format'] = $field['format'] ?? 'Y-m-d';
                    break;
            }

            $transformed[] = $transformedField;
        }

        return $transformed;
    }

    private function resolveChoices($choices): array
    {
        if (is_string($choices) && str_starts_with($choices, '@')) {
            // 处理动态选择项（如 @category_choices）
            $serviceName = substr($choices, 1);
            return $this->getChoicesFromService($serviceName);
        }

        return $choices;
    }

    private function getChoicesFromService(string $serviceName): array
    {
        // 根据服务名获取选择项
        // 这里可以注入各种选择项提供器
        return [];
    }
}
```

#### EasyAdmin 集成

```php
#[AsPermission(title: '优惠券条件管理')]
class CouponConditionAdminController extends AbstractCrudController
{
    public function __construct(
        private readonly ConditionManagerService $conditionManager,
        private readonly ConditionFormBuilder $formBuilder
    ) {}

    public static function getEntityFqcn(): string
    {
        return BaseCondition::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $addRequirement = Action::new('addRequirement', '添加领取条件')
            ->linkToCrudAction('addRequirement')
            ->setIcon('fa fa-plus');

        $addSatisfy = Action::new('addSatisfy', '添加使用条件')
            ->linkToCrudAction('addSatisfy')
            ->setIcon('fa fa-plus');

        return $actions
            ->add(Crud::PAGE_INDEX, $addRequirement)
            ->add(Crud::PAGE_INDEX, $addSatisfy);
    }

    public function addRequirement(AdminContext $context): Response
    {
        $couponId = $context->getRequest()->query->get('couponId');
        $types = $this->conditionManager->getAvailableRequirementTypes();

        return $this->render('admin/coupon/add_condition.html.twig', [
            'couponId' => $couponId,
            'types' => $types,
            'scenario' => 'requirement'
        ]);
    }

    public function addSatisfy(AdminContext $context): Response
    {
        $couponId = $context->getRequest()->query->get('couponId');
        $types = $this->conditionManager->getAvailableSatisfyTypes();

        return $this->render('admin/coupon/add_condition.html.twig', [
            'couponId' => $couponId,
            'types' => $types,
            'scenario' => 'satisfy'
        ]);
    }
}
```

## 迁移策略

### 阶段1：基础设施搭建（1-2周）

1. ✅ 创建核心接口和抽象类
2. ✅ 实现工厂和管理服务
3. ✅ 创建数据库迁移脚本
4. ✅ 编写基础单元测试

### 阶段2：现有条件类型迁移（2-3周）

1. ✅ 实现现有 RequirementType 对应的处理器
2. ✅ 实现现有 SatisfyType 对应的处理器
3. ✅ 编写数据迁移脚本
4. ✅ 确保现有功能正常工作

### 阶段3：服务层重构（1-2周）

1. ✅ 重构 CouponService 使用新的条件管理器
2. ✅ 更新相关的 Procedure 接口
3. ✅ 确保 API 向后兼容

### 阶段4：管理后台适配（2-3周）

1. ✅ 实现动态表单生成
2. ✅ 创建条件管理界面
3. ✅ 支持条件的增删改查
4. ✅ 实现条件预览功能

### 阶段5：向后兼容处理（1周）

1. ✅ 保留现有的枚举和实体（标记为 deprecated）
2. ✅ 实现数据格式转换
3. ✅ 提供迁移工具和文档

## 扩展示例

### 新增VIP等级条件

```php
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_vip_level')]
class VipLevelRequirement extends BaseRequirement
{
    #[ORM\Column(type: Types::INTEGER)]
    private int $minLevel = 1;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $allowedLevels = null;

    // 实现方法...
}

class VipLevelRequirementHandler implements RequirementHandlerInterface
{
    public function getType(): string
    {
        return 'vip_level';
    }

    public function getLabel(): string
    {
        return 'VIP等级限制';
    }

    public function checkRequirement(RequirementInterface $requirement, UserInterface $user, Coupon $coupon): bool
    {
        if (!$requirement instanceof VipLevelRequirement) {
            return false;
        }

        $userLevel = $user->getVipLevel(); // 假设用户有VIP等级

        if ($requirement->getAllowedLevels()) {
            if (!in_array($userLevel, $requirement->getAllowedLevels())) {
                throw new CouponRequirementException('VIP等级不符合要求');
            }
        } else {
            if ($userLevel < $requirement->getMinLevel()) {
                throw new CouponRequirementException("需要VIP{$requirement->getMinLevel()}等级或以上");
            }
        }

        return true;
    }

    // 其他方法实现...
}
```

只需要：

1. 创建实体类
2. 实现处理器类
3. 在 services.yaml 中注册
4. 后台会自动识别并提供配置界面

## 优势总结

### 1. 极强的扩展性

- 新增条件类型成本极低
- 完全插件化的架构
- 支持复杂的业务逻辑

### 2. 类型安全

- 专门的数据实体存储
- 编译时类型检查
- 运行时验证

### 3. 易于维护

- 条件逻辑完全独立
- 单一职责原则
- 便于单元测试

### 4. 前端友好

- 自动生成表单
- 动态字段配置
- 实时验证反馈

### 5. 向后兼容

- 现有API继续可用
- 平滑迁移过程
- 渐进式升级

### 6. 性能优化

- 基于 JOINED 继承的高效查询
- 条件处理器缓存
- 按需加载条件数据

### 7. 开发体验

- 清晰的接口约定
- 完整的类型提示
- 丰富的开发工具支持

这个重构方案完全解决了现有系统的问题，提供了极强的扩展性和灵活性，同时保持了良好的开发体验和向后兼容性。新增条件类型的成本降到最低，只需要实现接口和创建实体即可。
