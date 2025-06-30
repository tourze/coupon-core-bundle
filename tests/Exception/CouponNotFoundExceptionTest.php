<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;

class CouponNotFoundExceptionTest extends TestCase
{
    public function testExceptionIsThrowable(): void
    {
        $this->expectException(CouponNotFoundException::class);
        $this->expectExceptionMessage('Test message');
        
        throw new CouponNotFoundException('Test message');
    }
}