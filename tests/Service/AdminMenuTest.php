<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $linkGenerator = $this->createMock(\Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface::class);
        $adminMenu = new AdminMenu($linkGenerator);
        
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }
}