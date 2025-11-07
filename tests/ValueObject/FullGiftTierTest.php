<?php

namespace Tourze\CouponCoreBundle\Tests\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\ValueObject\FullGiftTier;

/**
 * @internal
 */
#[CoversClass(FullGiftTier::class)]
final class FullGiftTierTest extends TestCase
{
    public function testSortByThreshold(): void
    {
        $tiers = [
            FullGiftTier::fromArray([
                'threshold_amount' => '50',
                'gifts' => [['sku_id' => 'A', 'quantity' => 1]],
            ]),
            FullGiftTier::fromArray([
                'threshold_amount' => '100',
                'gifts' => [['sku_id' => 'B', 'quantity' => 1]],
            ]),
        ];

        $sorted = FullGiftTier::sortByThresholdDescending($tiers);

        self::assertSame('100.00', $sorted[0]->getThresholdAmount());
        self::assertSame('50.00', $sorted[1]->getThresholdAmount());
    }
}
