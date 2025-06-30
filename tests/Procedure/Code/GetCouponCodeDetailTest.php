<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Procedure\Code\GetCouponCodeDetail;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class GetCouponCodeDetailTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $normalizer = $this->createMock(NormalizerInterface::class);
        $security = $this->createMock(Security::class);
        
        $procedure = new GetCouponCodeDetail($codeRepository, $normalizer, $security);
        
        $this->assertInstanceOf(GetCouponCodeDetail::class, $procedure);
    }
}