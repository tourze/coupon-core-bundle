<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetAchCodeQrcodeLink;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

class GetAchCodeQrcodeLinkTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $normalizer = $this->createMock(NormalizerInterface::class);
        $security = $this->createMock(Security::class);
        
        $procedure = new GetAchCodeQrcodeLink($codeRepository, $urlGenerator, $normalizer, $security);
        
        $this->assertInstanceOf(GetAchCodeQrcodeLink::class, $procedure);
    }
}