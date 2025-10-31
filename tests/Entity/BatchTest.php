<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCoreBundle\Entity\Batch;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Batch::class)]
final class BatchTest extends AbstractEntityTestCase
{
    public function testInstanceCreation(): void
    {
        $batch = $this->createEntity();
        $this->assertNotNull($batch);
        $this->assertNull($batch->getId());
        $this->assertNull($batch->getTotalNum());
        $this->assertNull($batch->getSendNum());
    }

    public function testGetterAndSetterMethods(): void
    {
        $batch = $this->createEntity();
        $totalNum = 1000;
        $sendNum = 500;
        $remark = 'Test batch remark';
        $createdBy = 'admin';
        $updatedBy = 'moderator';

        $batch->setTotalNum($totalNum);
        $batch->setSendNum($sendNum);
        $batch->setRemark($remark);
        $batch->setCreatedBy($createdBy);
        $batch->setUpdatedBy($updatedBy);

        $this->assertEquals($totalNum, $batch->getTotalNum());
        $this->assertEquals($sendNum, $batch->getSendNum());
        $this->assertEquals($remark, $batch->getRemark());
        $this->assertEquals($createdBy, $batch->getCreatedBy());
        $this->assertEquals($updatedBy, $batch->getUpdatedBy());
    }

    public function testDatetimeProperties(): void
    {
        $batch = $this->createEntity();
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');

        $batch->setCreateTime($createTime);
        $batch->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $batch->getCreateTime());
        $this->assertEquals($updateTime, $batch->getUpdateTime());
    }

    public function testDatetimePropertiesWithNullValues(): void
    {
        $batch = $this->createEntity();
        $batch->setCreateTime(null);
        $batch->setUpdateTime(null);

        $this->assertNull($batch->getCreateTime());
        $this->assertNull($batch->getUpdateTime());
    }

    public function testCouponRelationship(): void
    {
        $batch = $this->createEntity();
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $batch->setCoupon($coupon);

        $this->assertSame($coupon, $batch->getCoupon());
    }

    public function testCouponRelationshipWithNull(): void
    {
        $batch = $this->createEntity();
        $batch->setCoupon(null);

        $this->assertNull($batch->getCoupon());
    }

    public function testTotalNumZero(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(0);
        $this->assertEquals(0, $batch->getTotalNum());
    }

    public function testTotalNumNegative(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(-1);
        $this->assertEquals(-1, $batch->getTotalNum());
    }

    public function testTotalNumLargeNumber(): void
    {
        $batch = $this->createEntity();
        $largeNumber = 999999999;
        $batch->setTotalNum($largeNumber);
        $this->assertEquals($largeNumber, $batch->getTotalNum());
    }

    public function testSendNumZero(): void
    {
        $batch = $this->createEntity();
        $batch->setSendNum(0);
        $this->assertEquals(0, $batch->getSendNum());
    }

    public function testSendNumNegative(): void
    {
        $batch = $this->createEntity();
        $batch->setSendNum(-1);
        $this->assertEquals(-1, $batch->getSendNum());
    }

    public function testSendNumEqualsTotalNum(): void
    {
        $batch = $this->createEntity();
        $num = 1000;
        $batch->setTotalNum($num);
        $batch->setSendNum($num);

        $this->assertEquals($num, $batch->getTotalNum());
        $this->assertEquals($num, $batch->getSendNum());
    }

    public function testSendNumGreaterThanTotalNum(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(100);
        $batch->setSendNum(150);

        $this->assertEquals(100, $batch->getTotalNum());
        $this->assertEquals(150, $batch->getSendNum());
    }

    public function testRemarkWithNull(): void
    {
        $batch = $this->createEntity();
        $batch->setRemark(null);
        $this->assertNull($batch->getRemark());
    }

    public function testRemarkWithEmptyString(): void
    {
        $batch = $this->createEntity();
        $batch->setRemark('');
        $this->assertEquals('', $batch->getRemark());
    }

    public function testRemarkWithLongText(): void
    {
        $batch = $this->createEntity();
        $longText = str_repeat('A', 1000);
        $batch->setRemark($longText);
        $this->assertEquals($longText, $batch->getRemark());
    }

    public function testFluentInterface(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(1000);
        $batch->setSendNum(500);
        $batch->setRemark('fluent test');
        $batch->setCreatedBy('user');
        $batch->setUpdatedBy('updater');

        $this->assertEquals(1000, $batch->getTotalNum());
        $this->assertEquals(500, $batch->getSendNum());
        $this->assertEquals('fluent test', $batch->getRemark());
        $this->assertEquals('user', $batch->getCreatedBy());
        $this->assertEquals('updater', $batch->getUpdatedBy());
    }

    public function testCreatedByWithNull(): void
    {
        $batch = $this->createEntity();
        $batch->setCreatedBy(null);
        $this->assertNull($batch->getCreatedBy());
    }

    public function testUpdatedByWithNull(): void
    {
        $batch = $this->createEntity();
        $batch->setUpdatedBy(null);
        $this->assertNull($batch->getUpdatedBy());
    }

    public function testCreatedByWithEmptyString(): void
    {
        $batch = $this->createEntity();
        $batch->setCreatedBy('');
        $this->assertEquals('', $batch->getCreatedBy());
    }

    public function testUpdatedByWithEmptyString(): void
    {
        $batch = $this->createEntity();
        $batch->setUpdatedBy('');
        $this->assertEquals('', $batch->getUpdatedBy());
    }

    public function testBatchCompletionPercentage(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(1000);
        $batch->setSendNum(250);

        $expectedPercentage = 25.0; // 250/1000 * 100
        $sendNum = $batch->getSendNum();
        $totalNum = $batch->getTotalNum();
        $this->assertNotNull($sendNum);
        $this->assertNotNull($totalNum);
        $this->assertNotEquals(0, $totalNum);
        $actualPercentage = ($sendNum / $totalNum) * 100;

        $this->assertEquals($expectedPercentage, $actualPercentage);
    }

    public function testBatchCompletionPercentageWithZeroTotal(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(0);
        $batch->setSendNum(0);

        // 避免除零错误的处理
        $total = $batch->getTotalNum();
        $sendNum = $batch->getSendNum();
        $this->assertNotNull($total);
        $this->assertNotNull($sendNum);
        if ($total > 0) {
            $percentage = ($sendNum / $total) * 100;
        } else {
            $percentage = 0;
        }

        $this->assertEquals(0, $percentage);
    }

    public function testRemainingQuantity(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(1000);
        $batch->setSendNum(300);

        $totalNum = $batch->getTotalNum();
        $sendNum = $batch->getSendNum();
        $this->assertNotNull($totalNum);
        $this->assertNotNull($sendNum);
        $remaining = $totalNum - $sendNum;
        $this->assertEquals(700, $remaining);
    }

    public function testRemainingQuantityWhenOversent(): void
    {
        $batch = $this->createEntity();
        $batch->setTotalNum(100);
        $batch->setSendNum(150);

        $totalNum = $batch->getTotalNum();
        $sendNum = $batch->getSendNum();
        $this->assertNotNull($totalNum);
        $this->assertNotNull($sendNum);
        $remaining = $totalNum - $sendNum;
        $this->assertEquals(-50, $remaining);
    }

    public function testSpecialCharactersInRemark(): void
    {
        $batch = $this->createEntity();
        $specialRemark = 'Batch with special chars: @#$%^&*()[]{}|\:";\'<>?,./ 中文测试';
        $batch->setRemark($specialRemark);
        $this->assertEquals($specialRemark, $batch->getRemark());
    }

    public function testMaximumIntegerValues(): void
    {
        $batch = $this->createEntity();
        $maxInt = PHP_INT_MAX;

        $batch->setTotalNum($maxInt);
        $batch->setSendNum($maxInt);

        $this->assertEquals($maxInt, $batch->getTotalNum());
        $this->assertEquals($maxInt, $batch->getSendNum());
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): Batch
    {
        return new Batch();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'totalNum' => ['totalNum', 1000];
        yield 'sendNum' => ['sendNum', 500];
        yield 'remark' => ['remark', 'Test batch remark'];
        yield 'createdBy' => ['createdBy', 'admin'];
        yield 'updatedBy' => ['updatedBy', 'moderator'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable('2023-01-01 10:00:00')];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable('2023-01-02 11:00:00')];
    }
}
