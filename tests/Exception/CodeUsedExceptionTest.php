<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Exception\CodeUsedException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CodeUsedException::class)]
final class CodeUsedExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return CodeUsedException::class;
    }
}
