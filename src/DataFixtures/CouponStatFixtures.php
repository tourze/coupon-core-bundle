<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\CouponStat;

/**
 * 优惠券统计数据填充
 * 为优惠券创建统计数据
 */
// // [When(env: 'test')]
#[When(env: 'dev')]
class CouponStatFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const STAT_CLOTHES_DISCOUNT = 'stat_clothes_discount';
    public const STAT_ELECTRONICS_DISCOUNT = 'stat_electronics_discount';
    public const STAT_RESTAURANT_DISCOUNT = 'stat_restaurant_discount';

    public function load(ObjectManager $manager): void
    {
        // 获取优惠券引用
        $basicDiscountCoupon = $this->getReference(CouponFixtures::COUPON_BASIC_DISCOUNT, Coupon::class);
        $shortTermCoupon = $this->getReference(CouponFixtures::COUPON_SHORT_TERM, Coupon::class);
        $longTermCoupon = $this->getReference(CouponFixtures::COUPON_LONG_TERM, Coupon::class);

        // 服装优惠券统计
        $clothesStat = new CouponStat();
        $clothesStat->setCouponId((string) $basicDiscountCoupon->getId());
        $clothesStat->setTotalNum(1000);
        $clothesStat->setReceivedNum(350);
        $clothesStat->setUsedNum(125);
        $clothesStat->setExpiredNum(15);
        $manager->persist($clothesStat);
        $this->addReference(self::STAT_CLOTHES_DISCOUNT, $clothesStat);

        // 数码产品优惠券统计
        $electronicsStat = new CouponStat();
        $electronicsStat->setCouponId((string) $shortTermCoupon->getId());
        $electronicsStat->setTotalNum(500);
        $electronicsStat->setReceivedNum(120);
        $electronicsStat->setUsedNum(45);
        $electronicsStat->setExpiredNum(8);
        $manager->persist($electronicsStat);
        $this->addReference(self::STAT_ELECTRONICS_DISCOUNT, $electronicsStat);

        // 餐厅折扣券统计
        $restaurantStat = new CouponStat();
        $restaurantStat->setCouponId((string) $longTermCoupon->getId());
        $restaurantStat->setTotalNum(2000);
        $restaurantStat->setReceivedNum(800);
        $restaurantStat->setUsedNum(320);
        $restaurantStat->setExpiredNum(25);
        $manager->persist($restaurantStat);
        $this->addReference(self::STAT_RESTAURANT_DISCOUNT, $restaurantStat);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CouponFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['coupon', 'test'];
    }
}
