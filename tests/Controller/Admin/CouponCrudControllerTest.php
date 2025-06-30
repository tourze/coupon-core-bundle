<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Controller\Admin\CouponCrudController;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CouponCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Coupon::class, CouponCrudController::getEntityFqcn());
    }
}