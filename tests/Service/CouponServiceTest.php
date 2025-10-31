<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Exception\CodeUsedException;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CouponService::class)]
#[RunTestsInSeparateProcesses]
final class CouponServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testDetectCouponWithValidSnShouldReturnCoupon(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();

        $couponSn = $coupon->getSn();
        $this->assertNotNull($couponSn, 'Coupon SN should not be null');
        $foundCoupon = $service->detectCoupon($couponSn);
        $this->assertSame($coupon->getId(), $foundCoupon->getId());
    }

    public function testDetectCouponWithValidIdShouldReturnCoupon(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();

        $foundCoupon = $service->detectCoupon((string) $coupon->getId());
        $this->assertSame($coupon->getId(), $foundCoupon->getId());
    }

    public function testDetectCouponWithInvalidIdShouldThrowException(): void
    {
        $service = self::getService(CouponService::class);

        $this->expectException(CouponNotFoundException::class);
        $this->expectExceptionMessage('找不到优惠券');

        $service->detectCoupon('INVALID_COUPON');
    }

    public function testGetCouponValidStockShouldReturnCorrectCount(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();

        $stock = $service->getCouponValidStock($coupon);
        $this->assertIsInt($stock);
        $this->assertGreaterThanOrEqual(0, $stock);
    }

    public function testCreateOneCodeShouldCreateValidCode(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();

        $code = $service->createOneCode($coupon);

        $this->assertNotNull($code->getId());
        $codeCurrentCoupon = $code->getCoupon();
        $this->assertNotNull($codeCurrentCoupon);
        $this->assertSame($coupon->getId(), $codeCurrentCoupon->getId());
        $this->assertNotEmpty($code->getSn());
        $this->assertTrue($code->isValid());
    }

    public function testPickCodeShouldAssignCodeToUser(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);

        $this->assertNotNull($code);
        $this->assertSame($user->getUserIdentifier(), $code->getOwner()?->getUserIdentifier());
        $this->assertNotNull($code->getGatherTime());
    }

    public function testLockCodeShouldSetLockState(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $this->assertFalse($code->isLocked());

        $service->lockCode($code);

        $this->assertTrue($code->isLocked());
    }

    public function testLockCodeWithUsedCodeShouldThrowException(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $service->redeemCode($code); // 先核销优惠券

        $this->expectException(CodeUsedException::class);
        $service->lockCode($code);
    }

    public function testUnlockCodeShouldRemoveLockState(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $service->lockCode($code);
        $this->assertTrue($code->isLocked());

        $service->unlockCode($code);

        $this->assertFalse($code->isLocked());
    }

    public function testUnlockCodeWithUsedCodeShouldThrowException(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $service->redeemCode($code); // 先核销优惠券

        $this->expectException(CodeUsedException::class);
        $service->unlockCode($code);
    }

    public function testMarkAsInvalidShouldSetInvalidState(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $this->assertTrue($code->isValid());

        $service->markAsInvalid($code);

        $this->assertFalse($code->isValid());
        $this->assertFalse($code->isLocked());
    }

    public function testRedeemCodeShouldSetUseTime(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $this->assertNull($code->getUseTime());

        $service->redeemCode($code);

        $this->assertNotNull($code->getUseTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $code->getUseTime());
    }

    public function testRedeemCodeWithAlreadyUsedCodeShouldThrowException(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->pickCode($user, $coupon);
        $this->assertNotNull($code);
        $service->redeemCode($code); // 先核销一次

        $this->expectException(CodeUsedException::class);
        $service->redeemCode($code); // 再次核销应该抛出异常
    }

    public function testSendCodeShouldReturnCodeWithUser(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $user = $this->createNormalUser('test@example.com', 'password');

        $code = $service->sendCode($user, $coupon);

        $this->assertNotNull($code);
        $this->assertSame($user->getUserIdentifier(), $code->getOwner()?->getUserIdentifier());
        $codeCurrentCoupon = $code->getCoupon();
        $this->assertNotNull($codeCurrentCoupon);
        $this->assertSame($coupon->getId(), $codeCurrentCoupon->getId());
        $this->assertNotNull($code->getGatherTime());
    }

    public function testUpdateTotalNumberShouldUpdateStatistics(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $couponId = (string) $coupon->getId();

        // 获取初始状态
        $statRepo = self::getService('Tourze\CouponCoreBundle\Repository\CouponStatRepository');
        $initialStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $initialTotal = null !== $initialStat ? $initialStat->getTotalNum() : 0;

        $service->updateTotalNumber($couponId, 5);

        // 清理实体管理器缓存以获取最新数据
        self::getEntityManager()->clear();
        $updatedStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $this->assertNotNull($updatedStat);
        $this->assertSame($initialTotal + 5, $updatedStat->getTotalNum());
    }

    public function testUpdateUsedNumberShouldUpdateStatistics(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $couponId = (string) $coupon->getId();

        // 先设置总数，避免更新被跳过
        $service->updateTotalNumber($couponId, 10);

        $statRepo = self::getService('Tourze\CouponCoreBundle\Repository\CouponStatRepository');
        // 清理缓存以获取设置后的状态
        self::getEntityManager()->clear();
        $initialStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $initialUsed = null !== $initialStat ? $initialStat->getUsedNum() : 0;

        $service->updateUsedNumber($couponId, 3);

        // 清理实体管理器缓存以获取最新数据
        self::getEntityManager()->clear();
        $updatedStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $this->assertNotNull($updatedStat);
        $this->assertSame($initialUsed + 3, $updatedStat->getUsedNum());
    }

    public function testUpdateReceivedNumberShouldUpdateStatistics(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $couponId = (string) $coupon->getId();

        // 先设置总数，避免更新被跳过
        $service->updateTotalNumber($couponId, 10);

        $statRepo = self::getService('Tourze\CouponCoreBundle\Repository\CouponStatRepository');
        // 清理缓存以获取设置后的状态
        self::getEntityManager()->clear();
        $initialStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $initialReceived = null !== $initialStat ? $initialStat->getReceivedNum() : 0;

        $service->updateReceivedNumber($couponId, 2);

        // 清理实体管理器缓存以获取最新数据
        self::getEntityManager()->clear();
        $updatedStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $this->assertNotNull($updatedStat);
        $this->assertSame($initialReceived + 2, $updatedStat->getReceivedNum());
    }

    public function testUpdateExpiredNumberShouldUpdateStatistics(): void
    {
        $service = self::getService(CouponService::class);
        $coupon = $this->createTestCoupon();
        $couponId = (string) $coupon->getId();

        // 先设置总数，避免更新被跳过
        $service->updateTotalNumber($couponId, 10);

        $statRepo = self::getService('Tourze\CouponCoreBundle\Repository\CouponStatRepository');
        // 清理缓存以获取设置后的状态
        self::getEntityManager()->clear();
        $initialStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $initialExpired = null !== $initialStat ? $initialStat->getExpiredNum() : 0;

        $service->updateExpiredNumber($couponId, 1);

        // 清理实体管理器缓存以获取最新数据
        self::getEntityManager()->clear();
        $updatedStat = $statRepo->findOneBy(['couponId' => $couponId]);
        $this->assertNotNull($updatedStat);
        $this->assertSame($initialExpired + 1, $updatedStat->getExpiredNum());
    }

    private function createTestCoupon(): Coupon
    {
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setSn('TEST_' . uniqid());
        $coupon->setValid(true);
        $coupon->setExpireDay(30);

        $entityManager = self::getEntityManager();
        $entityManager->persist($coupon);
        $entityManager->flush();

        return $coupon;
    }
}
