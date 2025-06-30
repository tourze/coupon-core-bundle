<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetUserCouponList;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;

class GetUserCouponListTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $couponRepository = $this->createMock(CouponRepository::class);
        $codeRepository = $this->createMock(CodeRepository::class);
        $normalizer = $this->createMock(NormalizerInterface::class);
        $security = $this->createMock(Security::class);
        
        $procedure = new GetUserCouponList($couponRepository, $codeRepository, $normalizer, $security);
        
        $this->assertInstanceOf(GetUserCouponList::class, $procedure);
    }
}