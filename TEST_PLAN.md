# CouponCoreBundle 测试计划

## 测试覆盖目标

达到 90% 以上的测试覆盖率，确保所有核心业务逻辑的正确性和稳定性。

## 测试执行命令

```bash
./vendor/bin/phpunit packages/coupon-core-bundle/tests
```

## 测试用例完成情况

### 1. Entity 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| Attribute.php | AttributeTest | ✅ 基础CRUD、关联关系、数组输出 | ✅ 已完成 | ✅ 通过 |
| Batch.php | BatchTest | ✅ 基础CRUD、券码统计、关联关系 | ✅ 已完成 | ✅ 通过 |
| Category.php | CategoryTest | ✅ 树形结构、有效期、排序 | ✅ 已完成 | ✅ 通过 |
| Channel.php | ChannelTest | ✅ 渠道配置、关联关系、数组输出 | ✅ 已完成 | ✅ 通过 |
| Code.php | CodeTest | ✅ 状态管理、过期检查、使用流程 | ✅ 已完成 | ✅ 通过 |
| Coupon.php | CouponTest | ✅ 基础配置、关联关系、选择项 | ✅ 已完成 | ✅ 通过 |
| CouponChannel.php | CouponChannelTest | ✅ 关联关系、配额管理 | ✅ 已完成 | ✅ 通过 |
| CouponStat.php | CouponStatTest | 🟡 统计数据、数量计算 | 🔄 待完成 | ⏳ 待测试 |
| Discount.php | DiscountTest | ✅ 抵扣配置、类型验证 | ✅ 已完成 | ✅ 通过 |
| ReadStatus.php | ReadStatusTest | 🟡 阅读状态、时间戳 | 🔄 待完成 | ⏳ 待测试 |
| Requirement.php | RequirementTest | ✅ 领取条件、规则验证 | ✅ 已完成 | ✅ 通过 |
| Satisfy.php | SatisfyTest | 🟡 使用条件、规则验证 | 🔄 待完成 | ⏳ 待测试 |

### 2. Repository 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| AttributeRepository.php | AttributeRepositoryTest | 🟡 基础查询、关联查询 | 🔄 待完成 | ⏳ 待测试 |
| BatchRepository.php | BatchRepositoryTest | 🟡 基础查询、统计查询 | 🔄 待完成 | ⏳ 待测试 |
| CategoryRepository.php | CategoryRepositoryTest | 🟡 树形查询、有效期过滤 | 🔄 待完成 | ⏳ 待测试 |
| ChannelRepository.php | ChannelRepositoryTest | 🟡 基础查询、编码查询 | 🔄 待完成 | ⏳ 待测试 |
| CodeRepository.php | CodeRepositoryTest | ✅ 用户券码查询、状态筛选 | ✅ 已完成 | ✅ 通过 |
| CouponChannelRepository.php | CouponChannelRepositoryTest | 🟡 关联查询、配额查询 | 🔄 待完成 | ⏳ 待测试 |
| CouponRepository.php | CouponRepositoryTest | 🟡 基础查询、分类查询 | 🔄 待完成 | ⏳ 待测试 |
| CouponStatRepository.php | CouponStatRepositoryTest | 🟡 统计查询、聚合计算 | 🔄 待完成 | ⏳ 待测试 |
| DiscountRepository.php | DiscountRepositoryTest | 🟡 基础查询、类型筛选 | 🔄 待完成 | ⏳ 待测试 |
| ReadStatusRepository.php | ReadStatusRepositoryTest | 🟡 状态查询、时间范围 | 🔄 待完成 | ⏳ 待测试 |
| RequirementRepository.php | RequirementRepositoryTest | 🟡 条件查询、类型筛选 | 🔄 待完成 | ⏳ 待测试 |
| SatisfyRepository.php | SatisfyRepositoryTest | 🟡 条件查询、类型筛选 | 🔄 待完成 | ⏳ 待测试 |

### 3. Service 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CodeService.php | CodeServiceTest | ✅ 库存计算、券码统计 | ✅ 已完成 | ✅ 通过 |
| CouponService.php | CouponServiceTest | ✅ 核心业务流程、规则验证 | ✅ 已完成 | ✅ 通过 |
| CouponResourceProvider.php | CouponResourceProviderTest | 🟡 资源提供、身份查找 | 🔄 待完成 | ⏳ 待测试 |

### 4. Enum 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CodeStatus.php | CodeStatusTest | 🟡 状态枚举、标签获取 | 🔄 待完成 | ⏳ 待测试 |
| DiscountType.php | DiscountTypeTest | 🟡 类型枚举、标签获取 | 🔄 待完成 | ⏳ 待测试 |
| RequirementType.php | RequirementTypeTest | 🟡 类型枚举、标签获取 | 🔄 待完成 | ⏳ 待测试 |
| SatisfyType.php | SatisfyTypeTest | 🟡 类型枚举、标签获取 | 🔄 待完成 | ⏳ 待测试 |

### 5. Event 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CodeLockedEvent.php | CodeLockedEventTest | 🟡 事件创建、属性访问 | 🔄 待完成 | ⏳ 待测试 |
| CodeNotFoundEvent.php | CodeNotFoundEventTest | 🟡 事件创建、用户信息 | 🔄 待完成 | ⏳ 待测试 |
| CodeRedeemEvent.php | CodeRedeemEventTest | 🟡 事件创建、额外数据 | 🔄 待完成 | ⏳ 待测试 |
| CodeUnlockEvent.php | CodeUnlockEventTest | 🟡 事件创建、属性访问 | 🔄 待完成 | ⏳ 待测试 |
| DetectCouponEvent.php | DetectCouponEventTest | 🟡 事件创建、券ID设置 | 🔄 待完成 | ⏳ 待测试 |
| SendCodeEvent.php | SendCodeEventTest | 🟡 事件创建、用户扩展 | 🔄 待完成 | ⏳ 待测试 |

### 6. Exception 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CodeNotFoundException.php | CodeNotFoundExceptionTest | 🟡 异常创建、券码信息 | 🔄 待完成 | ⏳ 待测试 |
| CodeUsedException.php | CodeUsedExceptionTest | 🟡 异常创建、基础属性 | 🔄 待完成 | ⏳ 待测试 |
| CouponNotFoundException.php | CouponNotFoundExceptionTest | 🟡 异常创建、基础属性 | 🔄 待完成 | ⏳ 待测试 |
| CouponRequirementException.php | CouponRequirementExceptionTest | 🟡 异常创建、基础属性 | 🔄 待完成 | ⏳ 待测试 |
| PickCodeNotFoundException.php | PickCodeNotFoundExceptionTest | 🟡 异常创建、基础属性 | 🔄 待完成 | ⏳ 待测试 |

### 7. MessageHandler 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CreateCodeHandler.php | CreateCodeHandlerTest | 🟡 消息处理、券码生成 | 🔄 待完成 | ⏳ 待测试 |

### 8. Message 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CreateCodeMessage.php | CreateCodeMessageTest | 🟡 消息创建、属性访问 | 🔄 待完成 | ⏳ 待测试 |

### 9. Traits 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CodeAware.php | CodeAwareTest | 🟡 Trait功能、属性访问 | 🔄 待完成 | ⏳ 待测试 |
| CouponAware.php | CouponAwareTest | 🟡 Trait功能、属性访问 | 🔄 待完成 | ⏳ 待测试 |

### 10. Command 层测试

| 文件 | 测试类 | 主要测试场景 | 完成状态 | 测试通过 |
|------|--------|-------------|----------|----------|
| CheckExpiredCategoryCommand.php | CheckExpiredCategoryCommandTest | 🟡 命令执行、数据库更新 | 🔄 待完成 | ⏳ 待测试 |
| RevokeExpiredCodeCommand.php | RevokeExpiredCodeCommandTest | 🟡 命令执行、批量处理 | 🔄 待完成 | ⏳ 待测试 |

## 图例说明

- ✅ 已完成并通过测试
- 🟡 已规划但未实现
- 🔄 正在进行中
- ❌ 测试失败需修复
- ⏳ 待测试
- 🏃 执行中

## 测试重点关注

1. **业务逻辑**：券码生成、发放、使用、过期等核心流程
2. **边界条件**：空值、极值、异常参数等场景
3. **并发安全**：锁定机制、状态一致性
4. **数据完整性**：关联关系、级联操作
5. **性能考虑**：大批量操作、复杂查询

## 当前进度

- 已完成：11/47 (23.4%)
- 进行中：0/47 (0%)
- 待完成：36/47 (76.6%)
