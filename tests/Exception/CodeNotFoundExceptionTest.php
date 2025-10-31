<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Exception\CodeNotFoundException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CodeNotFoundException::class)]
final class CodeNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return CodeNotFoundException::class;
    }
}
