<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Code\ActiveCouponCode;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class ActiveCouponCodeTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $procedure = new ActiveCouponCode($codeRepository, $security, $entityManager);
        
        $this->assertInstanceOf(ActiveCouponCode::class, $procedure);
    }
}