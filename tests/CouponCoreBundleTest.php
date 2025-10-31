<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\CouponCoreBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(CouponCoreBundle::class)]
#[RunTestsInSeparateProcesses]
final class CouponCoreBundleTest extends AbstractBundleTestCase
{
}
