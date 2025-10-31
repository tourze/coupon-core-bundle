<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Tourze\CouponCoreBundle\Procedure\Coupon\RedeemCoupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(RedeemCoupon::class)]
#[RunTestsInSeparateProcesses]
final class RedeemCouponTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(RedeemCoupon::class);
        $this->assertInstanceOf(RedeemCoupon::class, $procedure);
    }

    public function testExecute(): void
    {
        $codeRepository = self::getService(CodeRepository::class);
        $couponService = self::getService(CouponService::class);
        $logger = self::getService(LoggerInterface::class);
        $procedure = self::getService(RedeemCoupon::class);
        $result = $procedure->execute();

        $this->assertIsArray($result);
    }
}
