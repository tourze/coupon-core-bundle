<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeQrcodeTest extends AbstractEntityTestCase
{
    public function testQrcodeFunctionality(): void
    {
        $entity = $this->createEntity();
        $this->assertInstanceOf(Code::class, $entity);

        // 测试基本属性
        $entity->setSn('QRCODE-TEST-123');
        $this->assertEquals('QRCODE-TEST-123', $entity->getSn());

        // 测试有效状态
        $entity->setValid(true);
        $this->assertTrue($entity->isValid());

        // 测试锁定状态
        $entity->setLocked(true);
        $this->assertTrue($entity->isLocked());

        // 测试字符串表示 - 使用setId方法设置ID
        $entity->setId(456);
        $this->assertEquals('#456 QRCODE-TEST-123', (string) $entity);
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
            'sn' => ['sn', 'QRCODE-TEST-123'],
            'valid' => ['valid', true],
            'locked' => ['locked', false],
            'remark' => ['remark', '测试备注'],
        ];
    }
}
