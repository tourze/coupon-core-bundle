<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Code;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Code\ActiveCouponCode;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(ActiveCouponCode::class)]
#[RunTestsInSeparateProcesses]
final class ActiveCouponCodeTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(ActiveCouponCode::class);
        $this->assertNotNull($procedure);
    }

    public function testExecute(): void
    {
        $codeRepository = self::getService(CodeRepository::class);
        $security = self::getService(Security::class);
        $entityManager = self::getService(EntityManagerInterface::class);

        $procedure = self::getService(ActiveCouponCode::class);
        $procedure->code = 'test-code';

        // 模拟找不到券码的情况$this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到券码');

        $result = $procedure->execute();

        $this->assertIsArray($result);
    }
}
