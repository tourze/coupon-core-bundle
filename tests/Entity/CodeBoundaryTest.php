<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeBoundaryTest extends AbstractEntityTestCase
{
    public function testBoundaryConditions(): void
    {
        // 测试边界条件下的 getter 和 setter 方法
        $entity = $this->createEntity();
        $this->assertInstanceOf(Code::class, $entity);

        // 测试空字符串序列号
        $entity->setSn('');
        $this->assertEquals('', $entity->getSn());

        // 测试长序列号
        $longSn = str_repeat('A', 255);
        $entity->setSn($longSn);
        $this->assertEquals($longSn, $entity->getSn());

        // 测试布尔值边界
        $entity->setValid(true);
        $this->assertTrue($entity->isValid());

        $entity->setValid(false);
        $this->assertFalse($entity->isValid());
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
