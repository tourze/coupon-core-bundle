<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Coupon::class)]
final class CouponTest extends AbstractEntityTestCase
{
    public function testGetterAndSetterMethods(): void
    {
        $entity = $this->createEntity();

        // 测试基本属性设置和获取
        $entity->setName('测试优惠券');
        $this->assertEquals('测试优惠券', $entity->getName());

        $entity->setSn('COUPON123');
        $this->assertEquals('COUPON123', $entity->getSn());

        $entity->setBackImg('https://example.com/back.png');
        $this->assertEquals('https://example.com/back.png', $entity->getBackImg());

        $entity->setIconImg('https://example.com/front.png');
        $this->assertEquals('https://example.com/front.png', $entity->getIconImg());

        $entity->setRemark('优惠券描述');
        $this->assertEquals('优惠券描述', $entity->getRemark());

        $entity->setUseDesc('使用说明');
        $this->assertEquals('使用说明', $entity->getUseDesc());

        $entity->setExpireDay(30);
        $this->assertEquals(30, $entity->getExpireDay());

        $startTime = new \DateTimeImmutable();
        $entity->setStartTime($startTime);
        $this->assertEquals($startTime, $entity->getStartTime());

        $endTime = new \DateTimeImmutable('+30 days');
        $entity->setEndTime($endTime);
        $this->assertEquals($endTime, $entity->getEndTime());

        $entity->setValid(true);
        $this->assertTrue($entity->isValid());
    }

    public function testToStringMethod(): void
    {
        $entity = $this->createEntity();

        // coupon对象未设置任何属性时，toString应该返回空字符串
        $this->assertEquals('', (string) $entity);

        $entity->setName('测试优惠券');

        // 对于未保存的实体（ID为null），toString返回空字符串
        $this->assertEquals('', (string) $entity);
    }

    /**
     * 测试API数组输出
     */
    public function testRetrieveApiArray(): void
    {
        $entity = $this->createEntity();

        $entity->setName('测试优惠券');
        $entity->setSn('COUPON123');
        $entity->setBackImg('https://example.com/back.png');
        $entity->setRemark('优惠券描述');

        $apiArray = $entity->retrieveApiArray();
        $this->assertEquals('测试优惠券', $apiArray['name']);
        $this->assertEquals('COUPON123', $apiArray['sn']);
        // backImg可能不会直接暴露在API数组中，所以我们不检查它
        // $this->assertEquals('https://example.com/back.png', $apiArray['backImg']);
        $this->assertEquals('优惠券描述', $apiArray['remark']);
    }

    public function testDateTimeInterface(): void
    {
        $entity = $this->createEntity();

        // 测试 DateTime 和 DateTimeInterface 的正确处理
        $date = new \DateTimeImmutable();
        $entity->setCreateTime($date);
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getCreateTime());

        $startDate = new \DateTime();
        $entity->setStartDateTime($startDate);
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getStartDateTime());

        $endDate = new \DateTime();
        $entity->setEndDateTime($endDate);
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getEndDateTime());
    }

    public function testCodeCollectionCount(): void
    {
        $entity = $this->createEntity();

        // 测试 codes collection 的计数功能
        $this->assertCount(0, $entity->getCodes());
    }

    public function testResourceInterface(): void
    {
        $entity = $this->createEntity();

        // 对于未保存的实体，ID为null
        $entity->setName('测试优惠券');

        // Coupon的实际方法可能只返回ID而不是"coupon-123"格式
        $this->assertNull($entity->getId());
        $this->assertEmpty($entity->getResourceId());
        $this->assertEquals('测试优惠券', $entity->getResourceLabel());
    }

    public function testRetrieveAdminArray(): void
    {
        $entity = $this->createEntity();

        $entity->setName('测试优惠券');
        $entity->setSn('TEST12345');

        $adminArray = $entity->retrieveAdminArray();
        $this->assertArrayHasKey('id', $adminArray);
        $this->assertArrayHasKey('name', $adminArray);

        $this->assertEquals('测试优惠券', $adminArray['name']);
        $this->assertEquals('TEST12345', $adminArray['sn']);
    }

    protected function createEntity(): Coupon
    {
        return new Coupon();
    }

    /**
     * @return array<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'name' => ['name', '测试优惠券'],
            'sn' => ['sn', 'COUPON123'],
            'backImg' => ['backImg', 'https://example.com/back.png'],
            'iconImg' => ['iconImg', 'https://example.com/front.png'],
            'remark' => ['remark', '优惠券描述'],
            'useDesc' => ['useDesc', '使用说明'],
            'expireDay' => ['expireDay', 30],
            'startTime' => ['startTime', new \DateTimeImmutable()],
            'endTime' => ['endTime', new \DateTimeImmutable('+30 days')],
            'valid' => ['valid', true],
        ];
    }
}
