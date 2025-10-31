# coupon-core-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/coupon-core-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/coupon-core-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/tourze/coupon-core-bundle/php?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![License](https://img.shields.io/packagist/l/tourze/coupon-core-bundle?style=flat-square)](https://packagist.org/packages/tourze/coupon-core-bundle)
[![Code Coverage](https://codecov.io/gh/tourze/coupon-core-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/tourze/coupon-core-bundle)

A Symfony bundle providing core functionality for coupon management, including coupon categories, 
batches, codes, discounts, and channel management.

## Features

- **Coupon Management**: Comprehensive coupon lifecycle management
- **Batch Operations**: Create and manage coupon batches
- **Channel Integration**: Multi-channel coupon distribution
- **Discount Rules**: Flexible discount configurations
- **Automatic Expiration**: Scheduled tasks for handling expired coupons
- **Statistics Tracking**: Built-in coupon usage statistics

## Installation

```bash
composer require tourze/coupon-core-bundle
```

## Bundle Registration

Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Tourze\CouponCoreBundle\CouponCoreBundle::class => ['all' => true],
];
```

## Configuration

### Database Migration

Run migrations to create the necessary database tables:

```bash
php bin/console doctrine:migrations:migrate
```

### Console Commands

The bundle provides the following console commands:

#### Check Expired Categories

```bash
php bin/console coupon:check-expired-category
```

This command checks coupon categories for expiration and marks expired categories as invalid. 
It runs as a cron task every minute to ensure categories are automatically invalidated when they expire.

**Features:**
- Automatically marks categories as invalid when outside their valid time range
- Runs every minute via cron task integration
- Ensures coupon availability is properly controlled by time constraints

#### Revoke Expired Codes

```bash
php bin/console coupon:revoke-expired-code
```

This command automatically revokes expired coupon codes that haven't been used. 
It processes up to 500 codes per execution to manage system load.

**Features:**
- Finds unused coupon codes that have passed their expiration date
- Marks expired codes as invalid
- Processes in batches of 500 codes per run
- Prevents expired coupons from being redeemed

## Core Entities

### Category
Represents coupon categories with validation time ranges.

### Batch
Groups coupons for batch operations and management.

### Code
Individual coupon codes with unique identifiers and validation status.

### Coupon
The main coupon entity linking categories, batches, and codes.

### Channel
Distribution channels for coupon allocation.

### Discount
Discount rules and configurations for coupons.

### CouponStat
Statistical data for coupon usage tracking.

## Usage Examples

### Creating a Coupon Category

```php
use Tourze\CouponCoreBundle\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

$category = new Category();
$category->setName('Summer Sale');
$category->setStartTime(new \DateTime('2024-06-01'));
$category->setEndTime(new \DateTime('2024-08-31'));
$category->setValid(true);

$entityManager->persist($category);
$entityManager->flush();
```

### Generating Coupon Codes

```php
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Batch;

$batch = new Batch();
$batch->setName('SUMMER2024');
$batch->setCategory($category);

for ($i = 0; $i < 100; $i++) {
    $code = new Code();
    $code->setCode(generateUniqueCode()); // Your code generation logic
    $code->setBatch($batch);
    $code->setValid(true);
    $code->setExpireTime(new \DateTime('+30 days'));
    
    $entityManager->persist($code);
}

$entityManager->flush();
```

## Integration with Other Bundles

This bundle integrates with:
- `tourze/benefit-bundle` - For benefit calculations
- `tourze/condition-system-bundle` - For conditional coupon rules
- `tourze/doctrine-snowflake-bundle` - For unique ID generation
- `tourze/symfony-cron-job-bundle` - For scheduled tasks

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/coupon-core-bundle/tests
```

## Contributing

Please ensure all tests pass and code meets PHPStan level 8 standards before submitting pull requests.

## License

This bundle is released under the MIT License.