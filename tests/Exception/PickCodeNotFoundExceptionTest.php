<?php

namespace Tourze\CouponCoreBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Exception\PickCodeNotFoundException;

class PickCodeNotFoundExceptionTest extends TestCase
{
    public function testExceptionIsThrowable(): void
    {
        $this->expectException(PickCodeNotFoundException::class);
        $this->expectExceptionMessage('Test message');
        
        throw new PickCodeNotFoundException('Test message');
    }
}