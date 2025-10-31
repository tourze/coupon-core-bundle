<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Code::class)]
final class CodeTest extends AbstractEntityTestCase
{
    public function testMainCodeFunctionality(): void
    {
        $entity = $this->createEntity();
        $this->assertInstanceOf(Code::class, $entity);

        // 测试基本属性
        $entity->setSn('TEST-CODE-MAIN');
        $this->assertEquals('TEST-CODE-MAIN', $entity->getSn());

        // 测试核销次数
        $entity->setConsumeCount(1);
        $this->assertEquals(1, $entity->getConsumeCount());

        // 测试备注
        $entity->setRemark('测试备注');
        $this->assertEquals('测试备注', $entity->getRemark());

        // 测试时间设置
        $now = new \DateTimeImmutable();
        $entity->setGatherTime($now);
        $this->assertEquals($now, $entity->getGatherTime());

        $entity->setUseTime($now);
        $this->assertEquals($now, $entity->getUseTime());
    }

    protected function createEntity(): Code
    {
        return new Code();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     */
    /**
     * @return iterable<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'sn' => ['sn', 'TEST-CODE-123'];
        yield 'consumeCount' => ['consumeCount', 1];
        yield 'remark' => ['remark', '测试备注'];
        yield 'gatherTime' => ['gatherTime', new \DateTimeImmutable()];
        yield 'useTime' => ['useTime', new \DateTimeImmutable()];
    }
}
