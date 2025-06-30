<?php

namespace Tourze\CouponCoreBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Message\CreateCodeMessage;
use Tourze\CouponCoreBundle\MessageHandler\CreateCodeHandler;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

class CreateCodeHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $coupon = $this->createMock(Coupon::class);
        
        $couponRepository = $this->createMock(CouponRepository::class);
        $couponRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1, 'valid' => true])
            ->willReturn($coupon);
        
        $couponService = $this->createMock(CouponService::class);
        $couponService->expects($this->exactly(10))
            ->method('createOneCode')
            ->with($coupon);
        
        $message = new CreateCodeMessage();
        $message->setCouponId(1);
        $message->setQuantity(10);
        
        $handler = new CreateCodeHandler($couponRepository, $couponService);
        $handler($message);
    }
}