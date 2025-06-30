<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Controller\Admin\CategoryCrudController;
use Tourze\CouponCoreBundle\Entity\Category;

class CategoryCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Category::class, CategoryCrudController::getEntityFqcn());
    }
}