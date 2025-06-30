<?php

namespace Tourze\CouponCoreBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CouponCoreBundle\DependencyInjection\CouponCoreExtension;

class CouponCoreExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', '/test');
        $extension = new CouponCoreExtension();
        
        $extension->load([], $container);
        
        // 验证服务配置是否被正确加载
        $this->assertTrue($container->hasDefinition('Tourze\CouponCoreBundle\Service\CouponService'));
    }
}