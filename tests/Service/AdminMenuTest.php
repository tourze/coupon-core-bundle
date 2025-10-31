<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    public function testServiceCreation(): void
    {
        $service = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $service);
    }

    protected function onSetUp(): void
    {
        // 父类方法的实现
    }
}
