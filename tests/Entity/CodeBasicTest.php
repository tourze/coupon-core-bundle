<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeBasicTest extends AbstractEntityTestCase
{
    public function testBasicFunctionality(): void
    {
        $code = $this->createEntity();
        // 测试基本的 getter 和 setter 方法
        $this->assertInstanceOf(Code::class, $code);

        // 测试 ID getter
        $this->assertEquals(0, $code->getId());

        // 测试设置和获取序列号
        $testSn = 'TEST_CODE_123';
        $code->setSn($testSn);
        $this->assertEquals($testSn, $code->getSn());

        // 测试设置和获取有效状态
        $code->setValid(true);
        $this->assertTrue($code->isValid());

        $code->setValid(false);
        $this->assertFalse($code->isValid());
    }

    protected function createEntity(): Code
    {
        return new Code();
    }

    /**
     * @return array<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'sn' => ['sn', 'TEST_CODE_123'],
            'valid' => ['valid', true],
            'locked' => ['locked', false],
            'remark' => ['remark', '测试备注'],
        ];
    }
}
