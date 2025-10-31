<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeStatusTest extends AbstractEntityTestCase
{
    public function testStatusMethods(): void
    {
        $entity = $this->createEntity();
        $this->assertInstanceOf(Code::class, $entity);

        // 测试激活状态
        $entity->setActive(true);
        $this->assertTrue($entity->isActive());

        $entity->setActive(false);
        $this->assertFalse($entity->isActive());

        // 测试激活时间
        $activeTime = new \DateTimeImmutable();
        $entity->setActiveTime($activeTime);
        $this->assertEquals($activeTime, $entity->getActiveTime());

        // 测试需要激活状态
        $entity->setNeedActive(true);
        $this->assertTrue($entity->isNeedActive());
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
            'active' => ['active', true],
            'needActive' => ['needActive', false],
            'activeTime' => ['activeTime', new \DateTimeImmutable()],
        ];
    }
}
