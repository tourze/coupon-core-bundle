<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetUserCouponList;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetUserCouponList::class)]
#[RunTestsInSeparateProcesses]
final class GetUserCouponListTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置将在每个测试方法中处理
    }

    public function testCanInstantiate(): void
    {
        $procedure = self::getService(GetUserCouponList::class);
        $this->assertInstanceOf(GetUserCouponList::class, $procedure);
    }

    public function testExecute(): void
    {
        $procedure = self::getService(GetUserCouponList::class);

        // 设置 paginator 属性
        $paginator = self::getService(PaginatorInterface::class);
        $procedure->paginator = $paginator;

        // 由于需要用户登录，而当前测试环境没有用户，所以测试会抛出异常
        // 这是预期的行为
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User not authenticated');
        $procedure->execute();
    }
}
