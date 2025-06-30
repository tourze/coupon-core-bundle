<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Code\GetCouponChannelsByCode;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class GetCouponChannelsByCodeTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $security = $this->createMock(Security::class);
        
        $procedure = new GetCouponChannelsByCode($codeRepository, $security);
        
        $this->assertInstanceOf(GetCouponChannelsByCode::class, $procedure);
    }
}