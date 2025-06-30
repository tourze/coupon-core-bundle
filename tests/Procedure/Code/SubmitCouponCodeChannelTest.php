<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Code\SubmitCouponCodeChannel;
use Tourze\CouponCoreBundle\Repository\ChannelRepository;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

class SubmitCouponCodeChannelTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $codeRepository = $this->createMock(CodeRepository::class);
        $channelRepository = $this->createMock(ChannelRepository::class);
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $procedure = new SubmitCouponCodeChannel($codeRepository, $channelRepository, $security, $entityManager);
        
        $this->assertInstanceOf(SubmitCouponCodeChannel::class, $procedure);
    }
}