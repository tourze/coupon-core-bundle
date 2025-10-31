<?php

namespace Tourze\CouponCoreBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\DependencyInjection\CouponCoreExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(CouponCoreExtension::class)]
final class CouponCoreExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testExtensionCreation(): void
    {
        $extension = new CouponCoreExtension();
        $this->assertInstanceOf(CouponCoreExtension::class, $extension);
    }

    public function testGetAlias(): void
    {
        $extension = new CouponCoreExtension();
        $this->assertEquals('coupon_core', $extension->getAlias());
    }
}
