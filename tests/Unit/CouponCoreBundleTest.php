<?php

namespace Tourze\CouponCoreBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\CouponCoreBundle;

class CouponCoreBundleTest extends TestCase
{
    public function testGetBundleDependencies(): void
    {
        $expectedDependencies = [
            \Tourze\ConditionSystemBundle\ConditionSystemBundle::class => ['all' => true],
            \Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle::class => ['all' => true],
            \Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
            \Tourze\DoctrineUserBundle\DoctrineUserBundle::class => ['all' => true],
            \Tourze\Symfony\CronJob\CronJobBundle::class => ['all' => true],
        ];

        $this->assertSame($expectedDependencies, CouponCoreBundle::getBundleDependencies());
    }
}