<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $loader = new AttributeControllerLoader();
        
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }
}