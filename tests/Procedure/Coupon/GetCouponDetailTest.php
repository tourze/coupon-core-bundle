<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetCouponDetail;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;

class GetCouponDetailTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $couponRepository = $this->createMock(CouponRepository::class);
        $codeRepository = $this->createMock(CodeRepository::class);
        
        $procedure = new GetCouponDetail($couponRepository, $codeRepository);
        
        $this->assertInstanceOf(GetCouponDetail::class, $procedure);
    }
}