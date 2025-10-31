<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetCouponDetail;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetCouponDetail::class)]
#[RunTestsInSeparateProcesses]
final class GetCouponDetailTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(GetCouponDetail::class);
        $this->assertInstanceOf(GetCouponDetail::class, $procedure);
    }

    public function testExecuteWithNonExistentCoupon(): void
    {
        $procedure = self::getService(GetCouponDetail::class);
        $procedure->couponId = 'non-existent-coupon-id';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到优惠券');

        $procedure->execute();
    }

    public function testExecuteWithValidCoupon(): void
    {
        // 创建测试数据
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);

        $this->persistAndFlush($coupon);

        $procedure = self::getService(GetCouponDetail::class);
        $procedure->couponId = (string) $coupon->getId();

        $result = $procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('测试优惠券', $result['name']);
        $this->assertArrayHasKey('validCodeCount', $result);
        $this->assertEquals(0, $result['validCodeCount']); // 没有关联的券码
    }

    public function testExecuteWithInvalidCoupon(): void
    {
        // 创建无效的优惠券
        $coupon = new Coupon();
        $coupon->setName('无效优惠券');
        $coupon->setValid(false);
        $coupon->setExpireDay(30);

        $this->persistAndFlush($coupon);

        $procedure = self::getService(GetCouponDetail::class);
        $procedure->couponId = (string) $coupon->getId();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到优惠券');

        $procedure->execute();
    }
}
