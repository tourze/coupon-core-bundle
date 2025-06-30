<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\CouponCoreBundle\Procedure\Coupon\RedeemCoupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

class RedeemCouponTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $couponService = $this->createMock(CouponService::class);
        $logger = $this->createMock(LoggerInterface::class);
        
        $procedure = new RedeemCoupon($codeRepository, $couponService, $logger);
        
        $this->assertInstanceOf(RedeemCoupon::class, $procedure);
    }
}