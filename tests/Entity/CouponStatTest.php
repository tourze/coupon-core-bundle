<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\CouponStat;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(CouponStat::class)]
final class CouponStatTest extends AbstractEntityTestCase
{
    public function testBasicGettersAndSetters(): void
    {
        $entity = $this->createEntity();
        $this->assertInstanceOf(CouponStat::class, $entity);

        // 验证实体的基本结构
        $this->assertNull($entity->getId()); // 新创建的实体ID为null
        $this->assertNull($entity->getCreateTime()); // 新创建的实体createTime为null
        $this->assertNull($entity->getUpdateTime()); // 新创建的实体updateTime为null

        // 测试设置时间
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $entity->setCreateTime($createTime);
        $this->assertEquals($createTime, $entity->getCreateTime());

        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');
        $entity->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $entity->getUpdateTime());
    }

    protected function createEntity(): CouponStat
    {
        return new CouponStat();
    }

    /**
     * @return array<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'couponId' => ['couponId', 'COUPON_123'],
            'totalNum' => ['totalNum', 1000],
            'receivedNum' => ['receivedNum', 500],
            'usedNum' => ['usedNum', 200],
            'expiredNum' => ['expiredNum', 100],
        ];
    }
}
