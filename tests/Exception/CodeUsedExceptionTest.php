<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Exception\CodeUsedException;

class CodeUsedExceptionTest extends TestCase
{
    public function testExceptionIsThrowable(): void
    {
        $this->expectException(CodeUsedException::class);
        $this->expectExceptionMessage('Test message');
        
        throw new CodeUsedException('Test message');
    }
}