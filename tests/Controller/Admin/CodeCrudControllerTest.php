<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Controller\Admin\CodeCrudController;
use Tourze\CouponCoreBundle\Entity\Code;

class CodeCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Code::class, CodeCrudController::getEntityFqcn());
    }
}