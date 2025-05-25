# 优惠券核心模块 DataFixtures 使用指南

本文档介绍如何使用优惠券核心模块的数据填充功能。

## 概述

本模块提供了完整的优惠券系统测试数据，包括：
- 分类数据（Category）
- 渠道数据（Channel）
- 优惠券数据（Coupon）
- 优惠券属性（Attribute）
- 渠道配额（CouponChannel）

## 数据填充类列表

### 1. CategoryFixtures
创建优惠券分类测试数据，包含：
- 购物优惠（根分类）
  - 服装鞋帽（子分类）
  - 数码电器（子分类）
- 美食优惠（根分类）
  - 餐厅堂食（子分类）
  - 外卖配送（子分类）

### 2. ChannelFixtures
创建渠道测试数据，包含：
- 手机APP
- 微信小程序
- 支付宝小程序
- 官方网站
- 线下门店

### 3. CouponFixtures
创建优惠券测试数据，包含：
- 服装满减券20元（满100元可用）
- 数码产品满减券50元（满300元可用，需激活）
- 餐厅9折优惠券（堂食使用）
- 外卖85折优惠券（外卖使用，每日限用）
- 免配送费优惠券（当日有效）

### 4. AttributeFixtures
为优惠券添加自定义属性，包含：
- 折扣类型（固定金额/百分比/免配送费）
- 折扣金额/比例
- 使用条件（最低消费、分类限制等）
- 使用限制（每日限用、用户限用等）

### 5. CouponChannelFixtures
设置优惠券在各渠道的投放配额：
- 不同优惠券在不同渠道的配额数量
- 反映实际业务中的渠道投放策略

## 使用方法

### 环境要求
- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- Doctrine Fixtures Bundle 3.6+

### 安装依赖
```bash
# 在项目根目录执行
composer require --dev doctrine/doctrine-fixtures-bundle
```

### 执行数据填充
```bash
# 加载所有Fixtures（会清空现有数据）
php bin/console doctrine:fixtures:load

# 追加数据（不清空现有数据）
php bin/console doctrine:fixtures:load --append

# 仅加载特定Fixture类
php bin/console doctrine:fixtures:load --class=CategoryFixtures
```

### 数据依赖关系
DataFixtures按照以下顺序执行（通过DependentFixtureInterface管理）：
1. CategoryFixtures（独立）
2. ChannelFixtures（独立）
3. CouponFixtures（依赖Category和Channel）
4. AttributeFixtures（依赖Coupon）
5. CouponChannelFixtures（依赖Coupon和Channel）

## 测试数据说明

### 图片资源
所有图片资源使用Unsplash提供的高质量图片，确保：
- 图片质量高，适合演示使用
- 所有图片都是免费可用的
- 包含不同尺寸适配不同用途

### 数据特点
- **真实性**：模拟真实业务场景的优惠券数据
- **完整性**：覆盖优惠券系统的主要功能
- **多样性**：包含不同类型、不同场景的优惠券
- **关联性**：实体间关系完整，数据逻辑合理

## 自定义数据

### 修改现有数据
可以直接修改Fixture类中的数据创建代码：
```php
// 修改分类信息
$category = new Category();
$category->setTitle('自定义分类名称');
$category->setDescription('自定义描述');
// ...
```

### 添加新数据
在现有Fixture类中添加更多数据：
```php
// 在CategoryFixtures中添加新分类
$newCategory = new Category();
$newCategory->setTitle('新分类');
// ... 设置其他属性
$manager->persist($newCategory);
```

### 创建新Fixture类
```php
<?php
namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
// ...

class CustomFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 创建自定义数据
    }
    
    public function getDependencies(): array
    {
        return [
            CouponFixtures::class, // 依赖其他Fixture
        ];
    }
}
```

## 注意事项

1. **数据清除**：默认执行会清空数据库中的相关表数据
2. **依赖关系**：必须按照依赖关系正确执行
3. **生产环境**：请勿在生产环境中执行测试数据填充
4. **自动ID**：某些实体使用雪花ID生成器，ID值会自动生成
5. **时间字段**：创建时间和更新时间会自动设置

## 故障排除

### 常见问题
1. **外键约束错误**：检查依赖关系是否正确
2. **类型不匹配**：检查实体属性类型定义
3. **唯一约束冲突**：检查是否有重复的唯一字段值

### 调试建议
```bash
# 查看详细错误信息
php bin/console doctrine:fixtures:load -v

# 检查数据库连接
php bin/console doctrine:database:create --if-not-exists

# 更新数据库结构
php bin/console doctrine:migrations:migrate
```

## 相关资源

- [Doctrine Fixtures Bundle文档](https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html)
- [Symfony测试最佳实践](https://symfony.com/doc/current/testing.html)
- [Unsplash图片服务](https://unsplash.com/) 