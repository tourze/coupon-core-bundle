<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use CouponCode\CouponCode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Repository\CouponStatRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

class CouponServiceTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $couponRepository = $this->createMock(CouponRepository::class);
        $codeRepository = $this->createMock(CodeRepository::class);
        $codeGen = $this->createMock(CouponCode::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $couponStatRepository = $this->createMock(CouponStatRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $service = new CouponService(
            $couponRepository,
            $codeRepository,
            $codeGen,
            $eventDispatcher,
            $urlGenerator,
            $couponStatRepository,
            $entityManager
        );
        
        $this->assertInstanceOf(CouponService::class, $service);
    }
}