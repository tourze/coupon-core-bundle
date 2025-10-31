<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CouponNotFoundException::class)]
final class CouponNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return CouponNotFoundException::class;
    }
}
