<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;

/**
 * @internal
 */
#[CoversClass(CouponApplicationResult::class)]
final class CouponApplicationResultTest extends TestCase
{
    public function testMerge(): void
    {
        $first = new CouponApplicationResult('CODE', '5.00');
        $second = new CouponApplicationResult('CODE', '3.00');

        $merged = $first->merge($second);

        self::assertSame('8.00', $merged->getDiscountAmount());
    }
}
