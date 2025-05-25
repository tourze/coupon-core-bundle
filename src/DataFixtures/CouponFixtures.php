<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\CouponCoreBundle\Entity\Category;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 优惠券数据填充
 * 创建测试用的优惠券数据，关联到分类和渠道
 */
class CouponFixtures extends Fixture implements DependentFixtureInterface
{
    // 优惠券引用常量
    public const COUPON_DISCOUNT_20 = 'coupon-discount-20';
    public const COUPON_DISCOUNT_50 = 'coupon-discount-50';
    public const COUPON_PERCENT_10 = 'coupon-percent-10';
    public const COUPON_PERCENT_15 = 'coupon-percent-15';
    public const COUPON_FREE_SHIPPING = 'coupon-free-shipping';

    public function load(ObjectManager $manager): void
    {
        // 获取分类和渠道引用
        $clothesCategory = $this->getReference(CategoryFixtures::SUB_CATEGORY_CLOTHES, Category::class);
        $electronicsCategory = $this->getReference(CategoryFixtures::SUB_CATEGORY_ELECTRONICS, Category::class);
        $restaurantCategory = $this->getReference(CategoryFixtures::SUB_CATEGORY_RESTAURANT, Category::class);
        $takeawayCategory = $this->getReference(CategoryFixtures::SUB_CATEGORY_TAKEAWAY, Category::class);

        $appChannel = $this->getReference(ChannelFixtures::CHANNEL_APP, Channel::class);
        $wechatChannel = $this->getReference(ChannelFixtures::CHANNEL_WECHAT, Channel::class);
        $webChannel = $this->getReference(ChannelFixtures::CHANNEL_WEB, Channel::class);

        // 创建满减优惠券 - 服装类
        $discount20Coupon = new Coupon();
        $discount20Coupon->setName('服装满减券20元');
        $discount20Coupon->setCategory($clothesCategory);
        $discount20Coupon->setExpireDay(30);
        $discount20Coupon->setIconImg('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200');
        $discount20Coupon->setBackImg('https://images.unsplash.com/photo-1445205170230-053b83016050?w=600');
        $discount20Coupon->setRemark('购买服装类商品满100元可用');
        $discount20Coupon->setStartDateTime(new \DateTime('2024-01-01'));
        $discount20Coupon->setEndDateTime(new \DateTime('2024-12-31'));
        $discount20Coupon->setNeedActive(false);
        $discount20Coupon->setUseDesc('1. 仅限服装类商品使用\n2. 满100元可用\n3. 不与其他优惠叠加');
        $discount20Coupon->setStartTime(new \DateTime('2024-01-01'));
        $discount20Coupon->setEndTime(new \DateTime('2024-12-31'));
        $discount20Coupon->setValid(true);
        $discount20Coupon->addChannel($appChannel);
        $discount20Coupon->addChannel($wechatChannel);

        $manager->persist($discount20Coupon);

        // 创建满减优惠券 - 电子产品类
        $discount50Coupon = new Coupon();
        $discount50Coupon->setName('数码产品满减券50元');
        $discount50Coupon->setCategory($electronicsCategory);
        $discount50Coupon->setExpireDay(15);
        $discount50Coupon->setIconImg('https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=200');
        $discount50Coupon->setBackImg('https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=600');
        $discount50Coupon->setRemark('购买数码产品满300元可用');
        $discount50Coupon->setStartDateTime(new \DateTime('2024-01-01'));
        $discount50Coupon->setEndDateTime(new \DateTime('2024-06-30'));
        $discount50Coupon->setNeedActive(true);
        $discount50Coupon->setActiveValidDay(7);
        $discount50Coupon->setUseDesc('1. 仅限数码电器类商品使用\n2. 满300元可用\n3. 需要激活后使用');
        $discount50Coupon->setStartTime(new \DateTime('2024-01-01'));
        $discount50Coupon->setEndTime(new \DateTime('2024-06-30'));
        $discount50Coupon->setValid(true);
        $discount50Coupon->addChannel($webChannel);

        $manager->persist($discount50Coupon);

        // 创建折扣优惠券 - 餐厅类
        $percent10Coupon = new Coupon();
        $percent10Coupon->setName('餐厅9折优惠券');
        $percent10Coupon->setCategory($restaurantCategory);
        $percent10Coupon->setExpireDay(7);
        $percent10Coupon->setIconImg('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=200');
        $percent10Coupon->setBackImg('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600');
        $percent10Coupon->setRemark('餐厅堂食享9折优惠');
        $percent10Coupon->setStartDateTime(new \DateTime('2024-01-01'));
        $percent10Coupon->setEndDateTime(new \DateTime('2024-12-31'));
        $percent10Coupon->setNeedActive(false);
        $percent10Coupon->setUseDesc('1. 仅限餐厅堂食使用\n2. 不与其他优惠叠加\n3. 节假日通用');
        $percent10Coupon->setStartTime(new \DateTime('2024-01-01'));
        $percent10Coupon->setEndTime(new \DateTime('2024-12-31'));
        $percent10Coupon->setValid(true);
        $percent10Coupon->addChannel($appChannel);
        $percent10Coupon->addChannel($wechatChannel);

        $manager->persist($percent10Coupon);

        // 创建折扣优惠券 - 外卖类
        $percent15Coupon = new Coupon();
        $percent15Coupon->setName('外卖85折优惠券');
        $percent15Coupon->setCategory($takeawayCategory);
        $percent15Coupon->setExpireDay(3);
        $percent15Coupon->setIconImg('https://images.unsplash.com/photo-1526367790999-0150786686a2?w=200');
        $percent15Coupon->setBackImg('https://images.unsplash.com/photo-1526367790999-0150786686a2?w=600');
        $percent15Coupon->setRemark('外卖订单享85折优惠');
        $percent15Coupon->setStartDateTime(new \DateTime('2024-01-01'));
        $percent15Coupon->setEndDateTime(new \DateTime('2024-03-31'));
        $percent15Coupon->setNeedActive(false);
        $percent15Coupon->setUseDesc('1. 仅限外卖订单使用\n2. 满20元可用\n3. 每日限用一次');
        $percent15Coupon->setStartTime(new \DateTime('2024-01-01'));
        $percent15Coupon->setEndTime(new \DateTime('2024-03-31'));
        $percent15Coupon->setValid(true);
        $percent15Coupon->addChannel($appChannel);

        $manager->persist($percent15Coupon);

        // 创建免配送费优惠券
        $freeShippingCoupon = new Coupon();
        $freeShippingCoupon->setName('免配送费优惠券');
        $freeShippingCoupon->setCategory($takeawayCategory);
        $freeShippingCoupon->setExpireDay(1);
        $freeShippingCoupon->setIconImg('https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=200');
        $freeShippingCoupon->setBackImg('https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600');
        $freeShippingCoupon->setRemark('免除外卖配送费用');
        $freeShippingCoupon->setStartDateTime(new \DateTime('2024-01-01'));
        $freeShippingCoupon->setEndDateTime(new \DateTime('2024-12-31'));
        $freeShippingCoupon->setNeedActive(false);
        $freeShippingCoupon->setUseDesc('1. 免除配送费\n2. 不限最低消费\n3. 当日有效');
        $freeShippingCoupon->setStartTime(new \DateTime('2024-01-01'));
        $freeShippingCoupon->setEndTime(new \DateTime('2024-12-31'));
        $freeShippingCoupon->setValid(true);
        $freeShippingCoupon->addChannel($appChannel);
        $freeShippingCoupon->addChannel($wechatChannel);

        $manager->persist($freeShippingCoupon);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::COUPON_DISCOUNT_20, $discount20Coupon);
        $this->addReference(self::COUPON_DISCOUNT_50, $discount50Coupon);
        $this->addReference(self::COUPON_PERCENT_10, $percent10Coupon);
        $this->addReference(self::COUPON_PERCENT_15, $percent15Coupon);
        $this->addReference(self::COUPON_FREE_SHIPPING, $freeShippingCoupon);
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            ChannelFixtures::class,
        ];
    }
} 