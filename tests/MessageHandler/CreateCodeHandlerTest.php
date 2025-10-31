<?php

namespace Tourze\CouponCoreBundle\Tests\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Exception\CreateCodeException;
use Tourze\CouponCoreBundle\Message\CreateCodeMessage;
use Tourze\CouponCoreBundle\MessageHandler\CreateCodeHandler;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

/**
 * @internal
 */
#[CoversClass(CreateCodeHandler::class)]
final class CreateCodeHandlerTest extends TestCase
{
    /**
     * 创建 CouponRepository 的匿名实现
     */
    private function createCouponRepository(?Coupon $findOneByResult = null): CouponRepository
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        return new class($managerRegistry, $findOneByResult) extends CouponRepository {
            public function __construct(
                ManagerRegistry $registry,
                private ?Coupon $findOneByResult = null,
            ) {
                parent::__construct($registry);
            }

            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                return $this->findOneByResult;
            }
        };
    }

    /**
     * 由于 CouponService 是 readonly 类，不能继承，此处使用 Mock
     * 这是对 readonly 类的一个合理的例外情况
     */
    public function testHandlerCreation(): void
    {
        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);

        $handler = new CreateCodeHandler($couponRepository, $couponService);
        $this->assertInstanceOf(CreateCodeHandler::class, $handler);
    }

    public function testInvokeWithValidCoupon(): void
    {
        $coupon = new Coupon();
        $couponRepository = $this->createCouponRepository($coupon);
        $couponService = $this->createMock(CouponService::class);

        $couponService->expects($this->exactly(2))
            ->method('createOneCode')
            ->with($coupon)
        ;

        $message = new CreateCodeMessage();
        $message->setCouponId(123);
        $message->setQuantity(2);

        $handler = new CreateCodeHandler($couponRepository, $couponService);
        $handler($message);
    }

    public function testInvokeWithInvalidCouponThrowsException(): void
    {
        $couponRepository = $this->createCouponRepository(null);
        $couponService = $this->createMock(CouponService::class);

        $message = new CreateCodeMessage();
        $message->setCouponId(999);
        $message->setQuantity(1);

        $this->expectException(CreateCodeException::class);
        $this->expectExceptionMessage('生成code时，找不到优惠券');

        $handler = new CreateCodeHandler($couponRepository, $couponService);
        $handler($message);
    }
}
