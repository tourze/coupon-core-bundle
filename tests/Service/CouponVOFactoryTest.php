<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
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

    public function testCreateFromCoupon(): void
    {
        $factory = new CouponVOFactory();

        $coupon = new Coupon();
        $coupon->setSn('COUPON_SN');
        $coupon->setName('测试优惠券');
        $coupon->setType(CouponType::FULL_REDUCTION);
        $coupon->setConfiguration([
            'scope' => [],
            'condition' => ['threshold_amount' => '100.00'],
            'benefit' => ['discount_amount' => '20.00'],
        ]);

        $vo = $factory->createFromCoupon($coupon);

        self::assertInstanceOf(FullReductionCouponVO::class, $vo);
        self::assertSame('COUPON_SN', $vo->getCode());
        self::assertSame('测试优惠券', $vo->getName());
    }

    public function testCreateFromCouponCode(): void
    {
        $factory = new CouponVOFactory();

        $coupon = new Coupon();
        $coupon->setSn('COUPON_SN');
        $coupon->setName('测试优惠券');
        $coupon->setType(CouponType::FULL_REDUCTION);
        $coupon->setConfiguration([
            'scope' => [],
            'condition' => ['threshold_amount' => '100.00'],
            'benefit' => ['discount_amount' => '20.00'],
        ]);

        $code = new Code();
        $code->setCoupon($coupon);
        $code->setSn('CODE_SN');

        $vo = $factory->createFromCouponCode($code);

        self::assertInstanceOf(FullReductionCouponVO::class, $vo);
        self::assertSame('CODE_SN', $vo->getCode());
        self::assertSame('测试优惠券', $vo->getName());
    }
}
