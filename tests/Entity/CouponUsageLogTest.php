<?php

namespace Tourze\CouponCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\CouponUsageLog;

/**
 * @internal
 */
#[CoversClass(CouponUsageLog::class)]
final class CouponUsageLogTest extends TestCase
{
    public function testSetterGetter(): void
    {
        $log = new CouponUsageLog();
        $log->setCouponCode('CODE');
        $log->setUserIdentifier('user');
        $log->setOrderId(1);
        $log->setOrderNumber('NO');
        $log->setDiscountAmount('10.00');
        $log->setCouponType('full_reduction');

        self::assertSame('CODE', $log->getCouponCode());
        self::assertSame('user', $log->getUserIdentifier());
        self::assertSame(1, $log->getOrderId());
        self::assertSame('NO', $log->getOrderNumber());
        self::assertSame('10.00', $log->getDiscountAmount());
        self::assertSame('full_reduction', $log->getCouponType());
    }
}
