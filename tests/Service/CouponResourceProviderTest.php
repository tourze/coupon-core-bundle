<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponResourceProvider;
use Tourze\CouponCoreBundle\Service\CouponService;

class CouponResourceProviderTest extends TestCase
{
    public function testGetCode(): void
    {
        $couponRepository = $this->createMock(CouponRepository::class);
        $couponService = $this->createMock(CouponService::class);
        
        $provider = new CouponResourceProvider($couponRepository, $couponService);
        
        $this->assertSame('coupon', $provider->getCode());
    }
    
    public function testGetLabel(): void
    {
        $couponRepository = $this->createMock(CouponRepository::class);
        $couponService = $this->createMock(CouponService::class);
        
        $provider = new CouponResourceProvider($couponRepository, $couponService);
        
        $this->assertSame('优惠券', $provider->getLabel());
    }
}