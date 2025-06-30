<?php

namespace Tourze\CouponCoreBundle\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Traits\CodeAware;

class CodeAwareTest extends TestCase
{
    public function testGetSetCode(): void
    {
        $object = new class {
            use CodeAware;
        };
        
        $code = $this->createMock(Code::class);
        $object->setCode($code);
        
        $this->assertSame($code, $object->getCode());
    }
}