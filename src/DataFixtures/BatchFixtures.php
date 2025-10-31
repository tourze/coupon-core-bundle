<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCoreBundle\Entity\Batch;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 优惠券批次数据填充
 * 为优惠券创建批次数据
 */
// // [When(env: 'test')]
#[When(env: 'dev')]
class BatchFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const BATCH_CLOTHES_DISCOUNT = 'batch_clothes_discount';
    public const BATCH_ELECTRONICS_DISCOUNT = 'batch_electronics_discount';
    public const BATCH_RESTAURANT_DISCOUNT = 'batch_restaurant_discount';

    public function load(ObjectManager $manager): void
    {
        // 获取优惠券引用
        $basicDiscountCoupon = $this->getReference(CouponFixtures::COUPON_BASIC_DISCOUNT, Coupon::class);
        $shortTermCoupon = $this->getReference(CouponFixtures::COUPON_SHORT_TERM, Coupon::class);
        $longTermCoupon = $this->getReference(CouponFixtures::COUPON_LONG_TERM, Coupon::class);

        // 服装优惠券批次
        $clothesBatch = new Batch();
        $clothesBatch->setCoupon($basicDiscountCoupon);
        $clothesBatch->setTotalNum(1000);
        $clothesBatch->setSendNum(350);
        $clothesBatch->setRemark('服装类商品基础优惠券第一批');
        $clothesBatch->setCreatedBy('system');
        $clothesBatch->setUpdatedBy('system');
        $manager->persist($clothesBatch);
        $this->addReference(self::BATCH_CLOTHES_DISCOUNT, $clothesBatch);

        // 数码产品优惠券批次
        $electronicsBatch = new Batch();
        $electronicsBatch->setCoupon($shortTermCoupon);
        $electronicsBatch->setTotalNum(500);
        $electronicsBatch->setSendNum(120);
        $electronicsBatch->setRemark('数码电器类商品短期优惠券第一批');
        $electronicsBatch->setCreatedBy('system');
        $electronicsBatch->setUpdatedBy('system');
        $manager->persist($electronicsBatch);
        $this->addReference(self::BATCH_ELECTRONICS_DISCOUNT, $electronicsBatch);

        // 餐厅折扣券批次
        $restaurantBatch = new Batch();
        $restaurantBatch->setCoupon($longTermCoupon);
        $restaurantBatch->setTotalNum(2000);
        $restaurantBatch->setSendNum(800);
        $restaurantBatch->setRemark('餐厅长期优惠券第一批');
        $restaurantBatch->setCreatedBy('admin');
        $restaurantBatch->setUpdatedBy('admin');
        $manager->persist($restaurantBatch);
        $this->addReference(self::BATCH_RESTAURANT_DISCOUNT, $restaurantBatch);

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
