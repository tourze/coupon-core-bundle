<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Exception\CreateCodeException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CreateCodeException::class)]
final class CreateCodeExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return CreateCodeException::class;
    }
}
