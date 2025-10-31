<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 优惠券数据填充
 * 创建基础优惠券测试数据，专注于Coupon实体基本字段测试
 */
#[When(env: 'dev')]
class CouponFixtures extends Fixture implements FixtureGroupInterface
{
    public const COUPON_BASIC_DISCOUNT = 'coupon-basic-discount';
    public const COUPON_SHORT_TERM = 'coupon-short-term';
    public const COUPON_NEED_ACTIVE = 'coupon-need-active';
    public const COUPON_LONG_TERM = 'coupon-long-term';
    public const COUPON_INACTIVE = 'coupon-inactive';

    public function load(ObjectManager $manager): void
    {
        // 基础满减券
        $basicDiscountCoupon = new Coupon();
        $basicDiscountCoupon->setName('基础满减券');
        $basicDiscountCoupon->setExpireDay(30);
        $basicDiscountCoupon->setIconImg('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200');
        $basicDiscountCoupon->setBackImg('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600');
        $basicDiscountCoupon->setRemark('测试用基础满减券');
        $basicDiscountCoupon->setStartDateTime(new \DateTime('2024-01-01'));
        $basicDiscountCoupon->setEndDateTime(new \DateTime('2024-12-31'));
        $basicDiscountCoupon->setNeedActive(false);
        $basicDiscountCoupon->setUseDesc('1. 测试用优惠券\n2. 基础功能验证\n3. 无特殊限制');
        $basicDiscountCoupon->setStartTime(new \DateTime('2024-01-01'));
        $basicDiscountCoupon->setEndTime(new \DateTime('2024-12-31'));
        $basicDiscountCoupon->setValid(true);

        $manager->persist($basicDiscountCoupon);

        // 短期有效券
        $shortTermCoupon = new Coupon();
        $shortTermCoupon->setName('短期有效券');
        $shortTermCoupon->setExpireDay(7);
        $shortTermCoupon->setIconImg('https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=200');
        $shortTermCoupon->setBackImg('https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=600');
        $shortTermCoupon->setRemark('短期有效期测试券');
        $shortTermCoupon->setStartDateTime(new \DateTime('2024-01-01'));
        $shortTermCoupon->setEndDateTime(new \DateTime('2024-03-31'));
        $shortTermCoupon->setNeedActive(false);
        $shortTermCoupon->setUseDesc('1. 短期有效期测试\n2. 7天过期\n3. 季度结束');
        $shortTermCoupon->setStartTime(new \DateTime('2024-01-01'));
        $shortTermCoupon->setEndTime(new \DateTime('2024-03-31'));
        $shortTermCoupon->setValid(true);

        $manager->persist($shortTermCoupon);

        // 需要激活券
        $needActiveCoupon = new Coupon();
        $needActiveCoupon->setName('需要激活券');
        $needActiveCoupon->setExpireDay(15);
        $needActiveCoupon->setIconImg('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=200');
        $needActiveCoupon->setBackImg('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600');
        $needActiveCoupon->setRemark('需要激活功能测试券');
        $needActiveCoupon->setStartDateTime(new \DateTime('2024-01-01'));
        $needActiveCoupon->setEndDateTime(new \DateTime('2024-12-31'));
        $needActiveCoupon->setNeedActive(true);
        $needActiveCoupon->setActiveValidDay(10);
        $needActiveCoupon->setUseDesc('1. 需要激活后使用\n2. 激活后10天有效\n3. 激活功能测试');
        $needActiveCoupon->setStartTime(new \DateTime('2024-01-01'));
        $needActiveCoupon->setEndTime(new \DateTime('2024-12-31'));
        $needActiveCoupon->setValid(true);

        $manager->persist($needActiveCoupon);

        // 长期有效券
        $longTermCoupon = new Coupon();
        $longTermCoupon->setName('长期有效券');
        $longTermCoupon->setExpireDay(365);
        $longTermCoupon->setIconImg('https://images.unsplash.com/photo-1526367790999-0150786686a2?w=200');
        $longTermCoupon->setBackImg('https://images.unsplash.com/photo-1526367790999-0150786686a2?w=600');
        $longTermCoupon->setRemark('长期有效期测试券');
        $longTermCoupon->setStartDateTime(new \DateTime('2024-01-01'));
        $longTermCoupon->setEndDateTime(new \DateTime('2025-12-31'));
        $longTermCoupon->setNeedActive(false);
        $longTermCoupon->setUseDesc('1. 长期有效期测试\n2. 365天过期\n3. 跨年度有效');
        $longTermCoupon->setStartTime(new \DateTime('2024-01-01'));
        $longTermCoupon->setEndTime(new \DateTime('2025-12-31'));
        $longTermCoupon->setValid(true);

        $manager->persist($longTermCoupon);

        // 无效状态券
        $inactiveCoupon = new Coupon();
        $inactiveCoupon->setName('无效状态券');
        $inactiveCoupon->setExpireDay(30);
        $inactiveCoupon->setIconImg('https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=200');
        $inactiveCoupon->setBackImg('https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600');
        $inactiveCoupon->setRemark('无效状态测试券');
        $inactiveCoupon->setStartDateTime(new \DateTime('2024-01-01'));
        $inactiveCoupon->setEndDateTime(new \DateTime('2024-12-31'));
        $inactiveCoupon->setNeedActive(false);
        $inactiveCoupon->setUseDesc('1. 无效状态测试\n2. valid=false\n3. 状态切换测试');
        $inactiveCoupon->setStartTime(new \DateTime('2024-01-01'));
        $inactiveCoupon->setEndTime(new \DateTime('2024-12-31'));
        $inactiveCoupon->setValid(false);

        $manager->persist($inactiveCoupon);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::COUPON_BASIC_DISCOUNT, $basicDiscountCoupon);
        $this->addReference(self::COUPON_SHORT_TERM, $shortTermCoupon);
        $this->addReference(self::COUPON_NEED_ACTIVE, $needActiveCoupon);
        $this->addReference(self::COUPON_LONG_TERM, $longTermCoupon);
        $this->addReference(self::COUPON_INACTIVE, $inactiveCoupon);
    }

    public static function getGroups(): array
    {
        return ['coupon', 'test'];
    }
}
