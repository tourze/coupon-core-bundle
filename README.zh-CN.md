# coupon-core-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/coupon-core-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/coupon-core-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/tourze/coupon-core-bundle/php?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![License](https://img.shields.io/packagist/l/tourze/coupon-core-bundle?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![Code Coverage](https://codecov.io/gh/tourze/coupon-core-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/tourze/coupon-core-bundle)

一个提供优惠券核心功能的 Symfony Bundle，包括优惠券类别、批次、券码、折扣和渠道管理。

## 功能特性

- **优惠券管理**：全面的优惠券生命周期管理
- **批量操作**：创建和管理优惠券批次
- **渠道集成**：多渠道优惠券分发
- **折扣规则**：灵活的折扣配置
- **自动过期处理**：定时任务处理过期优惠券
- **统计跟踪**：内置优惠券使用统计

## 安装

```bash
composer require tourze/coupon-core-bundle
```

## Bundle 注册

在 `config/bundles.php` 中注册 Bundle：

```php
return [
    // ...
    Tourze\CouponCoreBundle\CouponCoreBundle::class => ['all' => true],
];
```

## 配置

### 数据库迁移

运行迁移以创建必要的数据库表：

```bash
php bin/console doctrine:migrations:migrate
```

### 控制台命令

该 Bundle 提供以下控制台命令：

#### 检查过期类别

```bash
php bin/console coupon:check-expired-category
```

此命令检查优惠券类别的有效期，并将过期的类别标记为无效。
它作为定时任务每分钟运行一次，确保类别在过期时自动失效。

**功能特点：**
- 自动将超出有效时间范围的类别标记为无效
- 通过定时任务集成每分钟运行一次
- 确保优惠券的可用性受时间约束的正确控制

#### 撤销过期券码

```bash
php bin/console coupon:revoke-expired-code
```

此命令自动撤销未使用的过期优惠券码。
每次执行处理最多 500 个券码以管理系统负载。

**功能特点：**
- 查找已过期但未使用的优惠券码
- 将过期券码标记为无效
- 每次运行批量处理 500 个券码
- 防止过期优惠券被兑换

## 核心实体

### Category（类别）
表示具有有效时间范围的优惠券类别。

### Batch（批次）
用于批量操作和管理的优惠券分组。

### Code（券码）
具有唯一标识符和验证状态的单个优惠券码。

### Coupon（优惠券）
连接类别、批次和券码的主要优惠券实体。

### Channel（渠道）
优惠券分配的分发渠道。

### Discount（折扣）
优惠券的折扣规则和配置。

### CouponStat（统计）
优惠券使用跟踪的统计数据。

## 使用示例

### 创建优惠券类别

```php
use Tourze\CouponCoreBundle\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

$category = new Category();
$category->setName('夏季促销');
$category->setStartTime(new \DateTime('2024-06-01'));
$category->setEndTime(new \DateTime('2024-08-31'));
$category->setValid(true);

$entityManager->persist($category);
$entityManager->flush();
```

### 生成优惠券码

```php
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Batch;

$batch = new Batch();
$batch->setName('SUMMER2024');
$batch->setCategory($category);

for ($i = 0; $i < 100; $i++) {
    $code = new Code();
    $code->setCode(generateUniqueCode()); // 您的券码生成逻辑
    $code->setBatch($batch);
    $code->setValid(true);
    $code->setExpireTime(new \DateTime('+30 days'));
    
    $entityManager->persist($code);
}

$entityManager->flush();
```

## 与其他 Bundle 的集成

此 Bundle 与以下组件集成：
- `tourze/benefit-bundle` - 用于权益计算
- `tourze/condition-system-bundle` - 用于条件优惠券规则
- `tourze/doctrine-snowflake-bundle` - 用于唯一 ID 生成
- `tourze/symfony-cron-job-bundle` - 用于定时任务

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/coupon-core-bundle/tests
```

## 贡献

在提交拉取请求之前，请确保所有测试通过并且代码符合 PHPStan 5 级标准。

## 许可证

此 Bundle 在 MIT 许可证下发布。