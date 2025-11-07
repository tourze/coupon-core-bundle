<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Exception\CouponEvaluationException;

/**
 * @internal
 */
#[CoversClass(CouponEvaluationException::class)]
final class CouponEvaluationExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new CouponEvaluationException('invalid');
        self::assertSame('invalid', $exception->getMessage());
    }
}
