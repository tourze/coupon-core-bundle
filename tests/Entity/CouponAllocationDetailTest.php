<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\CouponAllocationDetail;

/**
 * @internal
 */
#[CoversClass(CouponAllocationDetail::class)]
final class CouponAllocationDetailTest extends TestCase
{
    public function testSetterGetter(): void
    {
        $detail = new CouponAllocationDetail();
        $detail->setCouponCode('CODE');
        $detail->setOrderId(1);
        $detail->setOrderProductId(2);
        $detail->setSkuId('SKU1');
        $detail->setAllocatedAmount('5.00');
        $detail->setAllocationRule('proportional');

        self::assertSame('CODE', $detail->getCouponCode());
        self::assertSame(1, $detail->getOrderId());
        self::assertSame(2, $detail->getOrderProductId());
        self::assertSame('SKU1', $detail->getSkuId());
        self::assertSame('5.00', $detail->getAllocatedAmount());
        self::assertSame('proportional', $detail->getAllocationRule());
    }
}
