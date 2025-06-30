<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Code\UpdateCodeReadStatus;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineDirectInsertBundle\Service\DirectInsertService;

class UpdateCodeReadStatusTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $directInsertService = $this->createMock(DirectInsertService::class);
        $security = $this->createMock(Security::class);
        $logger = $this->createMock(LoggerInterface::class);
        
        $procedure = new UpdateCodeReadStatus($codeRepository, $directInsertService, $security, $logger);
        
        $this->assertInstanceOf(UpdateCodeReadStatus::class, $procedure);
    }
}