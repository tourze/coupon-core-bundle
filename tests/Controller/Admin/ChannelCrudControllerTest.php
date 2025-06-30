<?php

namespace Tourze\CouponCoreBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Controller\Admin\ChannelCrudController;
use Tourze\CouponCoreBundle\Entity\Channel;

class ChannelCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Channel::class, ChannelCrudController::getEntityFqcn());
    }
}