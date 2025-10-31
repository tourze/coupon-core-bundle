<?php

namespace Tourze\CouponCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * 券码数据填充
 * 创建测试用的券码数据，覆盖各种状态的券码场景
 */
#[When(env: 'dev')]
class CodeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const CODE_UNUSED_1 = 'code-unused-1';
    public const CODE_UNUSED_2 = 'code-unused-2';
    public const CODE_USED_1 = 'code-used-1';
    public const CODE_EXPIRED_1 = 'code-expired-1';
    public const CODE_NEED_ACTIVE_1 = 'code-need-active-1';
    public const CODE_LOCKED_1 = 'code-locked-1';

    public function load(ObjectManager $manager): void
    {
        $basicDiscountCoupon = $this->getReference(CouponFixtures::COUPON_BASIC_DISCOUNT, Coupon::class);
        $shortTermCoupon = $this->getReference(CouponFixtures::COUPON_SHORT_TERM, Coupon::class);
        $needActiveCoupon = $this->getReference(CouponFixtures::COUPON_NEED_ACTIVE, Coupon::class);
        $longTermCoupon = $this->getReference(CouponFixtures::COUPON_LONG_TERM, Coupon::class);
        $inactiveCoupon = $this->getReference(CouponFixtures::COUPON_INACTIVE, Coupon::class);

        // 创建未使用的券码
        $unusedCode1 = new Code();
        $unusedCode1->setSn('UNUSED20240101001');
        $unusedCode1->setCoupon($basicDiscountCoupon);
        $unusedCode1->setExpireTime(new \DateTime('+30 days'));
        $unusedCode1->setConsumeCount(1);
        $unusedCode1->setRemark('未使用的测试券码');
        $unusedCode1->setNeedActive(false);
        $unusedCode1->setActive(true);
        $unusedCode1->setValid(true);
        $unusedCode1->setLocked(false);

        $manager->persist($unusedCode1);

        // 创建需要激活的券码
        $unusedCode2 = new Code();
        $unusedCode2->setSn('INACTIVE20240101002');
        $unusedCode2->setCoupon($shortTermCoupon);
        $unusedCode2->setExpireTime(new \DateTime('+15 days'));
        $unusedCode2->setConsumeCount(1);
        $unusedCode2->setRemark('需要激活的测试券码');
        $unusedCode2->setNeedActive(true);
        $unusedCode2->setActive(false);
        $unusedCode2->setValid(true);
        $unusedCode2->setLocked(false);

        $manager->persist($unusedCode2);

        // 创建已使用的券码
        $usedCode1 = new Code();
        $usedCode1->setSn('USED20240101003');
        $usedCode1->setCoupon($needActiveCoupon);
        $usedCode1->setGatherTime(new \DateTime('-5 days'));
        $usedCode1->setExpireTime(new \DateTime('+2 days'));
        $usedCode1->setUseTime(new \DateTime('-1 day'));
        $usedCode1->setConsumeCount(1);
        $usedCode1->setRemark('已使用的测试券码');
        $usedCode1->setNeedActive(false);
        $usedCode1->setActive(true);
        $usedCode1->setValid(true);
        $usedCode1->setLocked(false);

        $manager->persist($usedCode1);

        // 创建已过期的券码
        $expiredCode1 = new Code();
        $expiredCode1->setSn('EXPIRED20240101004');
        $expiredCode1->setCoupon($longTermCoupon);
        $expiredCode1->setGatherTime(new \DateTime('-10 days'));
        $expiredCode1->setExpireTime(new \DateTime('-1 day'));
        $expiredCode1->setConsumeCount(1);
        $expiredCode1->setRemark('已过期的测试券码');
        $expiredCode1->setNeedActive(false);
        $expiredCode1->setActive(true);
        $expiredCode1->setValid(true);
        $expiredCode1->setLocked(false);

        $manager->persist($expiredCode1);

        // 创建需要激活且未激活的券码
        $needActiveCode1 = new Code();
        $needActiveCode1->setSn('NEEDACTIVE20240101005');
        $needActiveCode1->setCoupon($inactiveCoupon);
        $needActiveCode1->setGatherTime(new \DateTime('-2 days'));
        $needActiveCode1->setExpireTime(new \DateTime('+1 day'));
        $needActiveCode1->setConsumeCount(1);
        $needActiveCode1->setRemark('待激活的测试券码');
        $needActiveCode1->setNeedActive(true);
        $needActiveCode1->setActive(false);
        $needActiveCode1->setValid(true);
        $needActiveCode1->setLocked(false);

        $manager->persist($needActiveCode1);

        // 创建锁定的券码
        $lockedCode1 = new Code();
        $lockedCode1->setSn('LOCKED20240101006');
        $lockedCode1->setCoupon($basicDiscountCoupon);
        $lockedCode1->setExpireTime(new \DateTime('+30 days'));
        $lockedCode1->setConsumeCount(1);
        $lockedCode1->setRemark('锁定状态的测试券码');
        $lockedCode1->setNeedActive(false);
        $lockedCode1->setActive(true);
        $lockedCode1->setValid(true);
        $lockedCode1->setLocked(true);

        $manager->persist($lockedCode1);

        // 批量创建更多测试券码
        $this->createBatchCodes($manager, $basicDiscountCoupon, 'BATCH20240101', 10);
        $this->createBatchCodes($manager, $needActiveCoupon, 'PERCENT20240101', 15);
        $this->createBatchCodes($manager, $inactiveCoupon, 'SHIP20240101', 20);

        $manager->flush();

        $this->addReference(self::CODE_UNUSED_1, $unusedCode1);
        $this->addReference(self::CODE_UNUSED_2, $unusedCode2);
        $this->addReference(self::CODE_USED_1, $usedCode1);
        $this->addReference(self::CODE_EXPIRED_1, $expiredCode1);
        $this->addReference(self::CODE_NEED_ACTIVE_1, $needActiveCode1);
        $this->addReference(self::CODE_LOCKED_1, $lockedCode1);
    }

    private function createBatchCodes(
        ObjectManager $manager,
        Coupon $coupon,
        string $prefix,
        int $count,
    ): void {
        for ($i = 1; $i <= $count; ++$i) {
            $code = new Code();
            $code->setSn(sprintf('%s%03d', $prefix, $i));
            $code->setCoupon($coupon);
            $code->setExpireTime(new \DateTime('+' . (string) rand(7, 60) . ' days'));
            $code->setConsumeCount(1);
            $code->setRemark(sprintf('批量生成的测试券码 #%d', $i));
            $code->setNeedActive(1 === rand(0, 1));
            $code->setActive(1 === rand(0, 1));
            $code->setValid(true);
            $code->setLocked(false);

            // 30% 概率设置为已使用状态
            if (rand(1, 10) <= 3) {
                $code->setGatherTime(new \DateTime('-' . (string) rand(1, 10) . ' days'));
                $code->setUseTime(new \DateTime('-' . (string) rand(1, 5) . ' days'));
            }

            $manager->persist($code);
        }
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
