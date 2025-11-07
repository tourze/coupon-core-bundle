<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\Service\CouponVOFactory;
use Tourze\CouponCoreBundle\ValueObject\FullReductionCouponVO;

/**
 * @internal
 */
#[CoversClass(CouponVOFactory::class)]
final class CouponVOFactoryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $factory = new CouponVOFactory();

        $vo = $factory->createFromArray([
            'code' => 'CODE',
            'type' => CouponType::FULL_REDUCTION->value,
            'scope' => [],
            'condition' => ['threshold_amount' => '100.00'],
            'benefit' => ['discount_amount' => '20.00'],
        ]);

        self::assertInstanceOf(FullReductionCouponVO::class, $vo);
        self::assertSame('CODE', $vo->getCode());
    }
}
