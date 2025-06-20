<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\CouponChannel;

class CouponChannelTest extends TestCase
{
    private CouponChannel $couponChannel;

    protected function setUp(): void
    {
        $this->couponChannel = new CouponChannel();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(CouponChannel::class, $this->couponChannel);
        $this->assertNull($this->couponChannel->getId());
        $this->assertNull($this->couponChannel->getCoupon());
        $this->assertNull($this->couponChannel->getChannel());
        $this->assertNull($this->couponChannel->getQuota());
    }

    public function test_getter_and_setter_methods(): void
    {
        $quota = 1000;
        $createdBy = 'admin';
        $updatedBy = 'moderator';

        $this->couponChannel->setQuota($quota);
        $this->couponChannel->setCreatedBy($createdBy);
        $this->couponChannel->setUpdatedBy($updatedBy);

        $this->assertEquals($quota, $this->couponChannel->getQuota());
        $this->assertEquals($createdBy, $this->couponChannel->getCreatedBy());
        $this->assertEquals($updatedBy, $this->couponChannel->getUpdatedBy());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');

        $this->couponChannel->setCreateTime($createTime);
        $this->couponChannel->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->couponChannel->getCreateTime());
        $this->assertEquals($updateTime, $this->couponChannel->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->couponChannel->setCreateTime(null);
        $this->couponChannel->setUpdateTime(null);

        $this->assertNull($this->couponChannel->getCreateTime());
        $this->assertNull($this->couponChannel->getUpdateTime());
    }

    public function test_coupon_relationship(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');

        $this->couponChannel->setCoupon($coupon);

        $this->assertSame($coupon, $this->couponChannel->getCoupon());
    }

    public function test_coupon_relationship_with_null(): void
    {
        $this->couponChannel->setCoupon(null);

        $this->assertNull($this->couponChannel->getCoupon());
    }

    public function test_channel_relationship(): void
    {
        $channel = new Channel();
        $channel->setTitle('Test Channel');

        $this->couponChannel->setChannel($channel);

        $this->assertSame($channel, $this->couponChannel->getChannel());
    }

    public function test_channel_relationship_with_null(): void
    {
        $this->couponChannel->setChannel(null);

        $this->assertNull($this->couponChannel->getChannel());
    }

    public function test_quota_zero(): void
    {
        $this->couponChannel->setQuota(0);
        $this->assertEquals(0, $this->couponChannel->getQuota());
    }

    public function test_quota_negative(): void
    {
        $this->couponChannel->setQuota(-1);
        $this->assertEquals(-1, $this->couponChannel->getQuota());
    }

    public function test_quota_large_number(): void
    {
        $largeQuota = 999999999;
        $this->couponChannel->setQuota($largeQuota);
        $this->assertEquals($largeQuota, $this->couponChannel->getQuota());
    }

    public function test_quota_max_integer(): void
    {
        $maxInt = PHP_INT_MAX;
        $this->couponChannel->setQuota($maxInt);
        $this->assertEquals($maxInt, $this->couponChannel->getQuota());
    }

    public function test_quota_positive_number(): void
    {
        $positiveNumbers = [1, 100, 1000, 50000, 999999];

        foreach ($positiveNumbers as $number) {
            $this->couponChannel->setQuota($number);
            $this->assertEquals($number, $this->couponChannel->getQuota());
        }
    }

    public function test_fluent_interface(): void
    {
        $result = $this->couponChannel
            ->setQuota(1000)
            ->setCreatedBy('user')
            ->setUpdatedBy('updater');

        $this->assertSame($this->couponChannel, $result);
        $this->assertEquals(1000, $this->couponChannel->getQuota());
        $this->assertEquals('user', $this->couponChannel->getCreatedBy());
        $this->assertEquals('updater', $this->couponChannel->getUpdatedBy());
    }

    public function test_created_by_with_null(): void
    {
        $this->couponChannel->setCreatedBy(null);
        $this->assertNull($this->couponChannel->getCreatedBy());
    }

    public function test_updated_by_with_null(): void
    {
        $this->couponChannel->setUpdatedBy(null);
        $this->assertNull($this->couponChannel->getUpdatedBy());
    }

    public function test_created_by_with_empty_string(): void
    {
        $this->couponChannel->setCreatedBy('');
        $this->assertEquals('', $this->couponChannel->getCreatedBy());
    }

    public function test_updated_by_with_empty_string(): void
    {
        $this->couponChannel->setUpdatedBy('');
        $this->assertEquals('', $this->couponChannel->getUpdatedBy());
    }

    public function test_complete_relationship_setup(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Complete Test Coupon');

        $channel = new Channel();
        $channel->setTitle('Complete Test Channel');
        $channel->setCode('COMPLETE123');

        $quota = 5000;

        $this->couponChannel->setCoupon($coupon);
        $this->couponChannel->setChannel($channel);
        $this->couponChannel->setQuota($quota);

        $this->assertSame($coupon, $this->couponChannel->getCoupon());
        $this->assertSame($channel, $this->couponChannel->getChannel());
        $this->assertEquals($quota, $this->couponChannel->getQuota());
    }

    public function test_special_characters_in_user_fields(): void
    {
        $specialCreatedBy = 'user@domain.com';
        $specialUpdatedBy = 'admin_user-123';

        $this->couponChannel->setCreatedBy($specialCreatedBy);
        $this->couponChannel->setUpdatedBy($specialUpdatedBy);

        $this->assertEquals($specialCreatedBy, $this->couponChannel->getCreatedBy());
        $this->assertEquals($specialUpdatedBy, $this->couponChannel->getUpdatedBy());
    }

    public function test_relationship_consistency(): void
    {
        $coupon1 = new Coupon();
        $coupon1->setName('Coupon 1');
        $coupon2 = new Coupon();
        $coupon2->setName('Coupon 2');

        $channel1 = new Channel();
        $channel1->setTitle('Channel 1');
        $channel1->setCode('CH001');
        $channel2 = new Channel();
        $channel2->setTitle('Channel 2');
        $channel2->setCode('CH002');

        // 设置初始关系
        $this->couponChannel->setCoupon($coupon1);
        $this->couponChannel->setChannel($channel1);
        $this->assertSame($coupon1, $this->couponChannel->getCoupon());
        $this->assertSame($channel1, $this->couponChannel->getChannel());

        // 更改关系
        $this->couponChannel->setCoupon($coupon2);
        $this->couponChannel->setChannel($channel2);
        $this->assertSame($coupon2, $this->couponChannel->getCoupon());
        $this->assertSame($channel2, $this->couponChannel->getChannel());
    }

    public function test_quota_boundary_values(): void
    {
        $boundaryValues = [
            0,           // 最小有效值
            1,           // 最小正数
            100,         // 常见值
            999,         // 接近千位
            1000,        // 千位整数
            10000,       // 万位
            100000,      // 十万位
            1000000,     // 百万位
        ];

        foreach ($boundaryValues as $value) {
            $this->couponChannel->setQuota($value);
            $this->assertEquals($value, $this->couponChannel->getQuota());
        }
    }

    public function test_timestamp_consistency(): void
    {
        $now = new \DateTimeImmutable();
        $later = new \DateTimeImmutable('+1 hour');

        $this->couponChannel->setCreateTime($now);
        $this->couponChannel->setUpdateTime($later);

        $this->assertEquals($now, $this->couponChannel->getCreateTime());
        $this->assertEquals($later, $this->couponChannel->getUpdateTime());

        // 验证更新时间可以晚于创建时间
        $this->assertGreaterThan(
            $this->couponChannel->getCreateTime(),
            $this->couponChannel->getUpdateTime()
        );
    }
} 