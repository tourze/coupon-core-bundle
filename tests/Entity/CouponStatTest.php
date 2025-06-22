<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\CouponStat;

class CouponStatTest extends TestCase
{
    private CouponStat $couponStat;

    protected function setUp(): void
    {
        $this->couponStat = new CouponStat();
    }

    public function test_instance_creation(): void
    {
        $this->assertInstanceOf(CouponStat::class, $this->couponStat);
        $this->assertNull($this->couponStat->getId());
    }

    public function test_coupon_id_getter_and_setter(): void
    {
        $couponId = '123456789';
        
        $this->couponStat->setCouponId($couponId);
        
        $this->assertEquals($couponId, $this->couponStat->getCouponId());
    }

    public function test_total_num_getter_and_setter(): void
    {
        $totalNum = 1000;
        
        $this->couponStat->setTotalNum($totalNum);
        
        $this->assertEquals($totalNum, $this->couponStat->getTotalNum());
    }

    public function test_received_num_getter_and_setter(): void
    {
        $receivedNum = 500;
        
        $this->couponStat->setReceivedNum($receivedNum);
        
        $this->assertEquals($receivedNum, $this->couponStat->getReceivedNum());
    }

    public function test_used_num_getter_and_setter(): void
    {
        $usedNum = 200;
        
        $this->couponStat->setUsedNum($usedNum);
        
        $this->assertEquals($usedNum, $this->couponStat->getUsedNum());
    }

    public function test_expired_num_getter_and_setter(): void
    {
        $expiredNum = 100;
        
        $this->couponStat->setExpiredNum($expiredNum);
        
        $this->assertEquals($expiredNum, $this->couponStat->getExpiredNum());
    }

    public function test_datetime_properties(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');

        $this->couponStat->setCreateTime($createTime);
        $this->couponStat->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->couponStat->getCreateTime());
        $this->assertEquals($updateTime, $this->couponStat->getUpdateTime());
    }

    public function test_datetime_properties_with_null_values(): void
    {
        $this->couponStat->setCreateTime(null);
        $this->couponStat->setUpdateTime(null);

        $this->assertNull($this->couponStat->getCreateTime());
        $this->assertNull($this->couponStat->getUpdateTime());
    }

    public function test_complete_statistics_scenario(): void
    {
        $couponId = '987654321';
        $totalNum = 1000;
        $receivedNum = 800;
        $usedNum = 300;
        $expiredNum = 200;

        $this->couponStat->setCouponId($couponId);
        $this->couponStat->setTotalNum($totalNum);
        $this->couponStat->setReceivedNum($receivedNum);
        $this->couponStat->setUsedNum($usedNum);
        $this->couponStat->setExpiredNum($expiredNum);

        $this->assertEquals($couponId, $this->couponStat->getCouponId());
        $this->assertEquals($totalNum, $this->couponStat->getTotalNum());
        $this->assertEquals($receivedNum, $this->couponStat->getReceivedNum());
        $this->assertEquals($usedNum, $this->couponStat->getUsedNum());
        $this->assertEquals($expiredNum, $this->couponStat->getExpiredNum());

        // 检查计算逻辑是否合理（业务规则验证）
        $this->assertLessThanOrEqual($totalNum, $receivedNum, '已领取数量不应超过总数量');
        $this->assertLessThanOrEqual($receivedNum, $usedNum + $expiredNum, '已使用+已过期不应超过已领取');
    }

    public function test_zero_values(): void
    {
        $this->couponStat->setTotalNum(0);
        $this->couponStat->setReceivedNum(0);
        $this->couponStat->setUsedNum(0);
        $this->couponStat->setExpiredNum(0);

        $this->assertEquals(0, $this->couponStat->getTotalNum());
        $this->assertEquals(0, $this->couponStat->getReceivedNum());
        $this->assertEquals(0, $this->couponStat->getUsedNum());
        $this->assertEquals(0, $this->couponStat->getExpiredNum());
    }

    public function test_large_numbers(): void
    {
        $largeNumber = 999999999;

        $this->couponStat->setTotalNum($largeNumber);
        $this->couponStat->setReceivedNum($largeNumber);
        $this->couponStat->setUsedNum($largeNumber);
        $this->couponStat->setExpiredNum($largeNumber);

        $this->assertEquals($largeNumber, $this->couponStat->getTotalNum());
        $this->assertEquals($largeNumber, $this->couponStat->getReceivedNum());
        $this->assertEquals($largeNumber, $this->couponStat->getUsedNum());
        $this->assertEquals($largeNumber, $this->couponStat->getExpiredNum());
    }

    public function test_special_coupon_id_formats(): void
    {
        $specialIds = [
            '1',
            '999999999999999',
            'COUPON-ABC-123',
            'uuid-format-id-string',
        ];

        foreach ($specialIds as $id) {
            $this->couponStat->setCouponId($id);
            $this->assertEquals($id, $this->couponStat->getCouponId());
        }
    }

    public function test_statistics_calculations(): void
    {
        $totalNum = 1000;
        $receivedNum = 800;
        $usedNum = 300;
        $expiredNum = 100;

        $this->couponStat->setTotalNum($totalNum);
        $this->couponStat->setReceivedNum($receivedNum);
        $this->couponStat->setUsedNum($usedNum);
        $this->couponStat->setExpiredNum($expiredNum);

        // 计算剩余可领取数量
        $remainingToReceive = $totalNum - $receivedNum;
        $this->assertEquals(200, $remainingToReceive);

        // 计算未使用数量
        $unusedNum = $receivedNum - $usedNum - $expiredNum;
        $this->assertEquals(400, $unusedNum);

        // 计算使用率
        $usageRate = ($usedNum / $receivedNum) * 100;
        $this->assertEquals(37.5, $usageRate);
    }

    public function test_edge_case_all_received_used(): void
    {
        $totalNum = 100;

        $this->couponStat->setTotalNum($totalNum);
        $this->couponStat->setReceivedNum($totalNum);
        $this->couponStat->setUsedNum($totalNum);
        $this->couponStat->setExpiredNum(0);

        $this->assertEquals($totalNum, $this->couponStat->getTotalNum());
        $this->assertEquals($totalNum, $this->couponStat->getReceivedNum());
        $this->assertEquals($totalNum, $this->couponStat->getUsedNum());
        $this->assertEquals(0, $this->couponStat->getExpiredNum());
    }

    public function test_edge_case_all_received_expired(): void
    {
        $totalNum = 100;

        $this->couponStat->setTotalNum($totalNum);
        $this->couponStat->setReceivedNum($totalNum);
        $this->couponStat->setUsedNum(0);
        $this->couponStat->setExpiredNum($totalNum);

        $this->assertEquals($totalNum, $this->couponStat->getTotalNum());
        $this->assertEquals($totalNum, $this->couponStat->getReceivedNum());
        $this->assertEquals(0, $this->couponStat->getUsedNum());
        $this->assertEquals($totalNum, $this->couponStat->getExpiredNum());
    }

    public function test_partial_statistics_update(): void
    {
        // 模拟分步骤更新统计数据的场景
        $couponId = 'PARTIAL-UPDATE-TEST';
        
        // 第一步：设置总数
        $this->couponStat->setCouponId($couponId);
        $this->couponStat->setTotalNum(1000);
        
        $this->assertEquals(1000, $this->couponStat->getTotalNum());
        $this->assertEquals(0, $this->couponStat->getReceivedNum());
        
        // 第二步：更新已领取数
        $this->couponStat->setReceivedNum(500);
        $this->assertEquals(500, $this->couponStat->getReceivedNum());
        
        // 第三步：更新已使用数
        $this->couponStat->setUsedNum(200);
        $this->assertEquals(200, $this->couponStat->getUsedNum());
        
        // 第四步：更新已过期数
        $this->couponStat->setExpiredNum(50);
        $this->assertEquals(50, $this->couponStat->getExpiredNum());
    }
} 