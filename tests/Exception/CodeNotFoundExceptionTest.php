<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Exception\CodeNotFoundException;

class CodeNotFoundExceptionTest extends TestCase
{
    public function testExceptionIsThrowable(): void
    {
        $this->expectException(CodeNotFoundException::class);
        $this->expectExceptionMessage('Test message');
        
        throw new CodeNotFoundException('Test message');
    }
}