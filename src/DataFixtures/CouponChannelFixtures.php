<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\CouponChannel;

/**
 * 优惠券渠道配额数据填充
 * 为优惠券在不同渠道设置投放配额
 */
class CouponChannelFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 获取优惠券和渠道引用
        $discount20Coupon = $this->getReference(CouponFixtures::COUPON_DISCOUNT_20, Coupon::class);
        $discount50Coupon = $this->getReference(CouponFixtures::COUPON_DISCOUNT_50, Coupon::class);
        $percent10Coupon = $this->getReference(CouponFixtures::COUPON_PERCENT_10, Coupon::class);
        $percent15Coupon = $this->getReference(CouponFixtures::COUPON_PERCENT_15, Coupon::class);
        $freeShippingCoupon = $this->getReference(CouponFixtures::COUPON_FREE_SHIPPING, Coupon::class);

        $appChannel = $this->getReference(ChannelFixtures::CHANNEL_APP, Channel::class);
        $wechatChannel = $this->getReference(ChannelFixtures::CHANNEL_WECHAT, Channel::class);
        $alipayChannel = $this->getReference(ChannelFixtures::CHANNEL_ALIPAY, Channel::class);
        $webChannel = $this->getReference(ChannelFixtures::CHANNEL_WEB, Channel::class);
        $offlineChannel = $this->getReference(ChannelFixtures::CHANNEL_OFFLINE, Channel::class);

        // 服装满减券20元 - 多渠道投放
        $this->createCouponChannel($manager, $discount20Coupon, $appChannel, 1000);
        $this->createCouponChannel($manager, $discount20Coupon, $wechatChannel, 800);
        $this->createCouponChannel($manager, $discount20Coupon, $offlineChannel, 200);

        // 数码产品满减券50元 - 主要在网页端
        $this->createCouponChannel($manager, $discount50Coupon, $webChannel, 500);
        $this->createCouponChannel($manager, $discount50Coupon, $appChannel, 300);

        // 餐厅9折优惠券 - 多平台投放
        $this->createCouponChannel($manager, $percent10Coupon, $appChannel, 2000);
        $this->createCouponChannel($manager, $percent10Coupon, $wechatChannel, 1500);
        $this->createCouponChannel($manager, $percent10Coupon, $alipayChannel, 1000);
        $this->createCouponChannel($manager, $percent10Coupon, $offlineChannel, 500);

        // 外卖85折优惠券 - 主要在APP
        $this->createCouponChannel($manager, $percent15Coupon, $appChannel, 3000);
        $this->createCouponChannel($manager, $percent15Coupon, $wechatChannel, 1000);

        // 免配送费优惠券 - 移动端为主
        $this->createCouponChannel($manager, $freeShippingCoupon, $appChannel, 5000);
        $this->createCouponChannel($manager, $freeShippingCoupon, $wechatChannel, 3000);
        $this->createCouponChannel($manager, $freeShippingCoupon, $alipayChannel, 2000);

        $manager->flush();
    }

    /**
     * 创建优惠券渠道配额的辅助方法
     */
    private function createCouponChannel(ObjectManager $manager, Coupon $coupon, Channel $channel, int $quota): void
    {
        $couponChannel = new CouponChannel();
        $couponChannel->setCoupon($coupon);
        $couponChannel->setChannel($channel);
        $couponChannel->setQuota($quota);

        $manager->persist($couponChannel);
    }

    public function getDependencies(): array
    {
        return [
            CouponFixtures::class,
            ChannelFixtures::class,
        ];
    }
}
