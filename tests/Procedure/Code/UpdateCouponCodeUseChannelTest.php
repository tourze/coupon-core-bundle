<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Code\UpdateCouponCodeUseChannel;
use Tourze\CouponCoreBundle\Repository\ChannelRepository;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

class UpdateCouponCodeUseChannelTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $channelRepository = $this->createMock(ChannelRepository::class);
        
        $procedure = new UpdateCouponCodeUseChannel($codeRepository, $security, $entityManager, $channelRepository);
        
        $this->assertInstanceOf(UpdateCouponCodeUseChannel::class, $procedure);
    }
}