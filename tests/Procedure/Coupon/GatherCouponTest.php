<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Procedure\Coupon\GatherCoupon;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

class GatherCouponTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $couponRepository = $this->createMock(CouponRepository::class);
        $couponService = $this->createMock(CouponService::class);
        $normalizer = $this->createMock(NormalizerInterface::class);
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $procedure = new GatherCoupon($couponRepository, $couponService, $normalizer, $security, $entityManager);
        
        $this->assertInstanceOf(GatherCoupon::class, $procedure);
    }
}