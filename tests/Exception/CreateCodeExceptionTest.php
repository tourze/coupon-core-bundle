<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Exception\CreateCodeException;

class CreateCodeExceptionTest extends TestCase
{
    public function testExceptionIsRuntimeException(): void
    {
        $exception = new CreateCodeException('Test message');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
}