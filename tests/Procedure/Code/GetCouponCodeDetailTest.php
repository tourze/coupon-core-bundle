<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Procedure\Code\GetCouponCodeDetail;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetCouponCodeDetail::class)]
#[RunTestsInSeparateProcesses]
final class GetCouponCodeDetailTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(GetCouponCodeDetail::class);
        $this->assertInstanceOf(GetCouponCodeDetail::class, $procedure);
    }

    public function testExecute(): void
    {
        $codeRepository = self::getService(CodeRepository::class);
        $normalizer = self::getService(NormalizerInterface::class);
        $security = self::getService(Security::class);
        $procedure = self::getService(GetCouponCodeDetail::class);
        $procedure->codeId = '123';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到券码');

        $procedure->execute();
    }
}
