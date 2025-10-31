<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Exception\PickCodeNotFoundException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(PickCodeNotFoundException::class)]
final class PickCodeNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return PickCodeNotFoundException::class;
    }
}
