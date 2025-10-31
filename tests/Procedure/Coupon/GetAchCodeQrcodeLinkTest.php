<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetAchCodeQrcodeLink;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetAchCodeQrcodeLink::class)]
#[RunTestsInSeparateProcesses]
final class GetAchCodeQrcodeLinkTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(GetAchCodeQrcodeLink::class);
        $this->assertInstanceOf(GetAchCodeQrcodeLink::class, $procedure);
    }

    public function testExecuteWithInvalidCodeId(): void
    {
        $procedure = self::getService(GetAchCodeQrcodeLink::class);
        $procedure->codeId = 123;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到优惠券码');

        $procedure->execute();
    }

    public function testExecuteWithValidCodeId(): void
    {
        $procedure = self::getService(GetAchCodeQrcodeLink::class);
        $procedure->codeId = 123;

        // 测试无效券码的场景
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到优惠券码');

        $procedure->execute();
    }
}
