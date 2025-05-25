<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Batch;
use Tourze\CouponCoreBundle\Entity\Coupon;

class BatchTest extends TestCase
{
    private Batch $batch;

    protected function setUp(): void
    {
        $this->batch = new Batch();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(Batch::class, $this->batch);
        $this->assertNull($this->batch->getId());
        $this->assertNull($this->batch->getTotalNum());
        $this->assertNull($this->batch->getSendNum());
    }

    public function test_getter_and_setter_methods(): void
    {
        $totalNum = 1000;
        $sendNum = 500;
        $remark = 'Test batch remark';
        $createdBy = 'admin';
        $updatedBy = 'moderator';

        $this->batch->setTotalNum($totalNum);
        $this->batch->setSendNum($sendNum);
        $this->batch->setRemark($remark);
        $this->batch->setCreatedBy($createdBy);
        $this->batch->setUpdatedBy($updatedBy);

        $this->assertEquals($totalNum, $this->batch->getTotalNum());
        $this->assertEquals($sendNum, $this->batch->getSendNum());
        $this->assertEquals($remark, $this->batch->getRemark());
        $this->assertEquals($createdBy, $this->batch->getCreatedBy());
        $this->assertEquals($updatedBy, $this->batch->getUpdatedBy());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 11:00:00');

        $this->batch->setCreateTime($createTime);
        $this->batch->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->batch->getCreateTime());
        $this->assertEquals($updateTime, $this->batch->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->batch->setCreateTime(null);
        $this->batch->setUpdateTime(null);

        $this->assertNull($this->batch->getCreateTime());
        $this->assertNull($this->batch->getUpdateTime());
    }

    public function test_coupon_relationship(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $this->batch->setCoupon($coupon);

        $this->assertSame($coupon, $this->batch->getCoupon());
    }

    public function test_coupon_relationship_with_null(): void
    {
        $this->batch->setCoupon(null);

        $this->assertNull($this->batch->getCoupon());
    }

    public function test_total_num_zero(): void
    {
        $this->batch->setTotalNum(0);
        $this->assertEquals(0, $this->batch->getTotalNum());
    }

    public function test_total_num_negative(): void
    {
        $this->batch->setTotalNum(-1);
        $this->assertEquals(-1, $this->batch->getTotalNum());
    }

    public function test_total_num_large_number(): void
    {
        $largeNumber = 999999999;
        $this->batch->setTotalNum($largeNumber);
        $this->assertEquals($largeNumber, $this->batch->getTotalNum());
    }

    public function test_send_num_zero(): void
    {
        $this->batch->setSendNum(0);
        $this->assertEquals(0, $this->batch->getSendNum());
    }

    public function test_send_num_negative(): void
    {
        $this->batch->setSendNum(-1);
        $this->assertEquals(-1, $this->batch->getSendNum());
    }

    public function test_send_num_equals_total_num(): void
    {
        $num = 1000;
        $this->batch->setTotalNum($num);
        $this->batch->setSendNum($num);

        $this->assertEquals($num, $this->batch->getTotalNum());
        $this->assertEquals($num, $this->batch->getSendNum());
    }

    public function test_send_num_greater_than_total_num(): void
    {
        $this->batch->setTotalNum(100);
        $this->batch->setSendNum(150);

        $this->assertEquals(100, $this->batch->getTotalNum());
        $this->assertEquals(150, $this->batch->getSendNum());
    }

    public function test_remark_with_null(): void
    {
        $this->batch->setRemark(null);
        $this->assertNull($this->batch->getRemark());
    }

    public function test_remark_with_empty_string(): void
    {
        $this->batch->setRemark('');
        $this->assertEquals('', $this->batch->getRemark());
    }

    public function test_remark_with_long_text(): void
    {
        $longText = str_repeat('A', 1000);
        $this->batch->setRemark($longText);
        $this->assertEquals($longText, $this->batch->getRemark());
    }

    public function test_fluent_interface(): void
    {
        $result = $this->batch
            ->setTotalNum(1000)
            ->setSendNum(500)
            ->setRemark('fluent test')
            ->setCreatedBy('user')
            ->setUpdatedBy('updater');

        $this->assertSame($this->batch, $result);
        $this->assertEquals(1000, $this->batch->getTotalNum());
        $this->assertEquals(500, $this->batch->getSendNum());
        $this->assertEquals('fluent test', $this->batch->getRemark());
    }

    public function test_created_by_with_null(): void
    {
        $this->batch->setCreatedBy(null);
        $this->assertNull($this->batch->getCreatedBy());
    }

    public function test_updated_by_with_null(): void
    {
        $this->batch->setUpdatedBy(null);
        $this->assertNull($this->batch->getUpdatedBy());
    }

    public function test_created_by_with_empty_string(): void
    {
        $this->batch->setCreatedBy('');
        $this->assertEquals('', $this->batch->getCreatedBy());
    }

    public function test_updated_by_with_empty_string(): void
    {
        $this->batch->setUpdatedBy('');
        $this->assertEquals('', $this->batch->getUpdatedBy());
    }

    public function test_batch_completion_percentage(): void
    {
        $this->batch->setTotalNum(1000);
        $this->batch->setSendNum(250);

        $expectedPercentage = 25.0; // 250/1000 * 100
        $actualPercentage = ($this->batch->getSendNum() / $this->batch->getTotalNum()) * 100;

        $this->assertEquals($expectedPercentage, $actualPercentage);
    }

    public function test_batch_completion_percentage_with_zero_total(): void
    {
        $this->batch->setTotalNum(0);
        $this->batch->setSendNum(0);

        // 避免除零错误的处理
        $total = $this->batch->getTotalNum();
        if ($total > 0) {
            $percentage = ($this->batch->getSendNum() / $total) * 100;
        } else {
            $percentage = 0;
        }

        $this->assertEquals(0, $percentage);
    }

    public function test_remaining_quantity(): void
    {
        $this->batch->setTotalNum(1000);
        $this->batch->setSendNum(300);

        $remaining = $this->batch->getTotalNum() - $this->batch->getSendNum();
        $this->assertEquals(700, $remaining);
    }

    public function test_remaining_quantity_when_oversent(): void
    {
        $this->batch->setTotalNum(100);
        $this->batch->setSendNum(150);

        $remaining = $this->batch->getTotalNum() - $this->batch->getSendNum();
        $this->assertEquals(-50, $remaining);
    }

    public function test_special_characters_in_remark(): void
    {
        $specialRemark = 'Batch with special chars: @#$%^&*()[]{}|\\:";\'<>?,./ 中文测试';
        $this->batch->setRemark($specialRemark);
        $this->assertEquals($specialRemark, $this->batch->getRemark());
    }

    public function test_maximum_integer_values(): void
    {
        $maxInt = PHP_INT_MAX;

        $this->batch->setTotalNum($maxInt);
        $this->batch->setSendNum($maxInt);

        $this->assertEquals($maxInt, $this->batch->getTotalNum());
        $this->assertEquals($maxInt, $this->batch->getSendNum());
    }
}
