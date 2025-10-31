<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeLifecycleTest extends AbstractEntityTestCase
{
    public function testLifecycleMethods(): void
    {
        $entity = $this->createEntity();
        $this->assertInstanceOf(Code::class, $entity);

        // 测试基本属性
        $entity->setSn('TEST-CODE-123');
        $this->assertEquals('TEST-CODE-123', $entity->getSn());

        // 测试状态属性
        $entity->setValid(true);
        $this->assertTrue($entity->isValid());

        // 测试过期时间
        $expiryDate = new \DateTimeImmutable('+7 days');
        $entity->setExpireTime($expiryDate);
        $this->assertEquals($expiryDate, $entity->getExpireTime());
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
            'expireTime' => ['expireTime', new \DateTimeImmutable('+7 days')],
        ];
    }
}
