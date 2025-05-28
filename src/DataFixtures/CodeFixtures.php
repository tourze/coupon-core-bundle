<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 券码数据填充
 * 为不同优惠券创建测试用的券码数据，包含各种状态的券码
 */
class CodeFixtures extends Fixture implements DependentFixtureInterface
{
    // 券码引用常量
    public const CODE_UNUSED_1 = 'code-unused-1';
    public const CODE_UNUSED_2 = 'code-unused-2';
    public const CODE_USED_1 = 'code-used-1';
    public const CODE_EXPIRED_1 = 'code-expired-1';
    public const CODE_NEED_ACTIVE_1 = 'code-need-active-1';
    public const CODE_LOCKED_1 = 'code-locked-1';

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

        // 创建未使用的券码 - 服装满减券
        $unusedCode1 = new Code();
        $unusedCode1->setSn('CLOTHES20240101001');
        $unusedCode1->setCoupon($discount20Coupon);
        $unusedCode1->setChannel($appChannel);
        $unusedCode1->setGatherChannel($appChannel);
        $unusedCode1->setExpireTime(new \DateTime('+30 days'));
        $unusedCode1->setConsumeCount(1);
        $unusedCode1->setRemark('服装满减券测试码');
        $unusedCode1->setNeedActive(false);
        $unusedCode1->setActive(true);
        $unusedCode1->setValid(true);
        $unusedCode1->setLocked(false);

        $manager->persist($unusedCode1);

        // 创建未使用的券码 - 数码产品满减券（需要激活）
        $unusedCode2 = new Code();
        $unusedCode2->setSn('DIGITAL20240101002');
        $unusedCode2->setCoupon($discount50Coupon);
        $unusedCode2->setChannel($webChannel);
        $unusedCode2->setGatherChannel($webChannel);
        $unusedCode2->setExpireTime(new \DateTime('+15 days'));
        $unusedCode2->setConsumeCount(1);
        $unusedCode2->setRemark('数码产品满减券测试码');
        $unusedCode2->setNeedActive(true);
        $unusedCode2->setActive(false);
        $unusedCode2->setValid(true);
        $unusedCode2->setLocked(false);

        $manager->persist($unusedCode2);

        // 创建已使用的券码 - 餐厅折扣券
        $usedCode1 = new Code();
        $usedCode1->setSn('RESTAURANT20240101003');
        $usedCode1->setCoupon($percent10Coupon);
        $usedCode1->setChannel($wechatChannel);
        $usedCode1->setGatherChannel($wechatChannel);
        $usedCode1->setUseChannel($offlineChannel);
        $usedCode1->setGatherTime(new \DateTime('-5 days'));
        $usedCode1->setExpireTime(new \DateTime('+2 days'));
        $usedCode1->setUseTime(new \DateTime('-1 day'));
        $usedCode1->setConsumeCount(1);
        $usedCode1->setRemark('餐厅折扣券已使用');
        $usedCode1->setNeedActive(false);
        $usedCode1->setActive(true);
        $usedCode1->setValid(true);
        $usedCode1->setLocked(false);

        $manager->persist($usedCode1);

        // 创建已过期的券码 - 外卖折扣券
        $expiredCode1 = new Code();
        $expiredCode1->setSn('TAKEAWAY20240101004');
        $expiredCode1->setCoupon($percent15Coupon);
        $expiredCode1->setChannel($appChannel);
        $expiredCode1->setGatherChannel($appChannel);
        $expiredCode1->setGatherTime(new \DateTime('-10 days'));
        $expiredCode1->setExpireTime(new \DateTime('-1 day'));
        $expiredCode1->setConsumeCount(1);
        $expiredCode1->setRemark('外卖折扣券已过期');
        $expiredCode1->setNeedActive(false);
        $expiredCode1->setActive(true);
        $expiredCode1->setValid(true);
        $expiredCode1->setLocked(false);

        $manager->persist($expiredCode1);

        // 创建需要激活的券码 - 免配送费券
        $needActiveCode1 = new Code();
        $needActiveCode1->setSn('FREESHIP20240101005');
        $needActiveCode1->setCoupon($freeShippingCoupon);
        $needActiveCode1->setChannel($alipayChannel);
        $needActiveCode1->setGatherChannel($alipayChannel);
        $needActiveCode1->setGatherTime(new \DateTime('-2 days'));
        $needActiveCode1->setExpireTime(new \DateTime('+1 day'));
        $needActiveCode1->setConsumeCount(1);
        $needActiveCode1->setRemark('免配送费券待激活');
        $needActiveCode1->setNeedActive(true);
        $needActiveCode1->setActive(false);
        $needActiveCode1->setValid(true);
        $needActiveCode1->setLocked(false);

        $manager->persist($needActiveCode1);

        // 创建锁定的券码 - 服装满减券
        $lockedCode1 = new Code();
        $lockedCode1->setSn('LOCKED20240101006');
        $lockedCode1->setCoupon($discount20Coupon);
        $lockedCode1->setChannel($offlineChannel);
        $lockedCode1->setGatherChannel($offlineChannel);
        $lockedCode1->setExpireTime(new \DateTime('+30 days'));
        $lockedCode1->setConsumeCount(1);
        $lockedCode1->setRemark('锁定状态的券码');
        $lockedCode1->setNeedActive(false);
        $lockedCode1->setActive(true);
        $lockedCode1->setValid(true);
        $lockedCode1->setLocked(true);

        $manager->persist($lockedCode1);

        // 批量创建更多测试券码
        $this->createBatchCodes($manager, $discount20Coupon, $appChannel, 'BATCH20240101', 10);
        $this->createBatchCodes($manager, $percent10Coupon, $wechatChannel, 'WECHAT20240101', 15);
        $this->createBatchCodes($manager, $freeShippingCoupon, $alipayChannel, 'ALIPAY20240101', 20);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::CODE_UNUSED_1, $unusedCode1);
        $this->addReference(self::CODE_UNUSED_2, $unusedCode2);
        $this->addReference(self::CODE_USED_1, $usedCode1);
        $this->addReference(self::CODE_EXPIRED_1, $expiredCode1);
        $this->addReference(self::CODE_NEED_ACTIVE_1, $needActiveCode1);
        $this->addReference(self::CODE_LOCKED_1, $lockedCode1);
    }

    /**
     * 批量创建券码的辅助方法
     */
    private function createBatchCodes(
        ObjectManager $manager,
        Coupon $coupon,
        Channel $channel,
        string $prefix,
        int $count
    ): void {
        for ($i = 1; $i <= $count; $i++) {
            $code = new Code();
            $code->setSn(sprintf('%s%03d', $prefix, $i));
            $code->setCoupon($coupon);
            $code->setChannel($channel);
            $code->setGatherChannel($channel);
            $code->setExpireTime(new \DateTime('+' . rand(7, 60) . ' days'));
            $code->setConsumeCount(1);
            $code->setRemark(sprintf('批量生成的测试券码 #%d', $i));
            $code->setNeedActive(rand(0, 1) === 1);
            $code->setActive(rand(0, 1) === 1);
            $code->setValid(true);
            $code->setLocked(false);

            // 随机设置一些券码为已使用状态
            if (rand(1, 10) <= 3) { // 30% 概率已使用
                $code->setGatherTime(new \DateTime('-' . rand(1, 10) . ' days'));
                $code->setUseTime(new \DateTime('-' . rand(1, 5) . ' days'));
                $code->setUseChannel($channel);
            }

            $manager->persist($code);
        }
    }

    public function getDependencies(): array
    {
        return [
            CouponFixtures::class,
            ChannelFixtures::class,
        ];
    }
} 