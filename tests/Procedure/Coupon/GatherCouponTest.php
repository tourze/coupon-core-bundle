<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Procedure\Coupon\GatherCoupon;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GatherCoupon::class)]
#[RunTestsInSeparateProcesses]
final class GatherCouponTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(GatherCoupon::class);
        $this->assertInstanceOf(GatherCoupon::class, $procedure);
    }

    public function testExecute(): void
    {
        $couponRepository = self::getService(CouponRepository::class);
        $couponService = self::getService(CouponService::class);
        $normalizer = self::getService(NormalizerInterface::class);
        $security = self::getService(Security::class);
        $entityManager = self::getService(EntityManagerInterface::class);
        $procedure = self::getService(GatherCoupon::class);
        $procedure->couponId = 'test-coupon-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到指定优惠券');

        $result = $procedure->execute();

        $this->assertIsArray($result);
    }
}
