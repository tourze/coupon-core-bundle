<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\CouponCoreBundle\Entity\Channel;

/**
 * 渠道数据填充
 * 创建测试用的渠道数据，包含不同类型的渠道
 */
class ChannelFixtures extends Fixture
{
    // 渠道引用常量
    public const CHANNEL_APP = 'channel-app';
    public const CHANNEL_WECHAT = 'channel-wechat';
    public const CHANNEL_ALIPAY = 'channel-alipay';
    public const CHANNEL_WEB = 'channel-web';
    public const CHANNEL_OFFLINE = 'channel-offline';

    public function load(ObjectManager $manager): void
    {
        // 创建APP渠道
        $appChannel = new Channel();
        $appChannel->setTitle('手机APP');
        $appChannel->setRemark('移动端应用程序渠道');
        $appChannel->setLogo('https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400');
        $appChannel->setRedirectUrl('app://coupon/list');
        $appChannel->setAppId('');
        $appChannel->setValid(true);

        $manager->persist($appChannel);

        // 创建微信小程序渠道
        $wechatChannel = new Channel();
        $wechatChannel->setTitle('微信小程序');
        $wechatChannel->setRemark('微信小程序渠道，支持微信生态');
        $wechatChannel->setLogo('https://images.unsplash.com/photo-1611262588024-d12430b98920?w=400');
        $wechatChannel->setRedirectUrl('pages/coupon/index');
        $wechatChannel->setAppId('wx1234567890abcdef');
        $wechatChannel->setValid(true);

        $manager->persist($wechatChannel);

        // 创建支付宝小程序渠道
        $alipayChannel = new Channel();
        $alipayChannel->setTitle('支付宝小程序');
        $alipayChannel->setRemark('支付宝小程序渠道');
        $alipayChannel->setLogo('https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=400');
        $alipayChannel->setRedirectUrl('pages/coupon/index');
        $alipayChannel->setAppId('2021001234567890');
        $alipayChannel->setValid(true);

        $manager->persist($alipayChannel);

        // 创建网页端渠道
        $webChannel = new Channel();
        $webChannel->setTitle('官方网站');
        $webChannel->setRemark('PC端官方网站渠道');
        $webChannel->setLogo('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400');
        $webChannel->setRedirectUrl('https://www.example.com/coupons');
        $webChannel->setAppId('');
        $webChannel->setValid(true);

        $manager->persist($webChannel);

        // 创建线下渠道
        $offlineChannel = new Channel();
        $offlineChannel->setTitle('线下门店');
        $offlineChannel->setRemark('线下实体门店渠道');
        $offlineChannel->setLogo('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400');
        $offlineChannel->setRedirectUrl('');
        $offlineChannel->setAppId('');
        $offlineChannel->setValid(true);

        $manager->persist($offlineChannel);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::CHANNEL_APP, $appChannel);
        $this->addReference(self::CHANNEL_WECHAT, $wechatChannel);
        $this->addReference(self::CHANNEL_ALIPAY, $alipayChannel);
        $this->addReference(self::CHANNEL_WEB, $webChannel);
        $this->addReference(self::CHANNEL_OFFLINE, $offlineChannel);
    }
} 