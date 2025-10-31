<?php

namespace Tourze\CouponCoreBundle\Tests\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Traits\CodeAware;

/**
 * @internal
 */
#[CoversClass(CodeAware::class)]
final class CodeAwareTest extends TestCase
{
    public function testGetSetCode(): void
    {
        $object = new class {
            use CodeAware;
        };

        // 使用直接实例化替代Mock以满足静态分析要求：
        // 理由 1: Code 是内部实体类，不应该被Mock
        // 理由 2: 直接实例化能够更真实地测试trait与实体的交互
        // 理由 3: 避免Mock具体实现类，提高代码质量
        $code = new Code();
        $object->setCode($code);

        $this->assertSame($code, $object->getCode());
    }
}
