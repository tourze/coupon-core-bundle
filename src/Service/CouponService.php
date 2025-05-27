<?php

namespace Tourze\CouponCoreBundle\Service;

use Carbon\Carbon;
use CouponCode\CouponCode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\CouponStat;
use Tourze\CouponCoreBundle\Event\CodeLockedEvent;
use Tourze\CouponCoreBundle\Event\CodeNotFoundEvent;
use Tourze\CouponCoreBundle\Event\CodeRedeemEvent;
use Tourze\CouponCoreBundle\Event\CodeUnlockEvent;
use Tourze\CouponCoreBundle\Event\DetectCouponEvent;
use Tourze\CouponCoreBundle\Event\SendCodeEvent;
use Tourze\CouponCoreBundle\Exception\CodeNotFoundException;
use Tourze\CouponCoreBundle\Exception\CodeUsedException;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;
use Tourze\CouponCoreBundle\Exception\PickCodeNotFoundException;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Repository\CouponStatRepository;

/**
 * 优惠券服务
 */
#[Autoconfigure(public: true)]
class CouponService
{
    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CodeRepository $codeRepository,
        private readonly CouponCode $codeGen,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CouponStatRepository $couponStatRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 根据入参读取指定的优惠券信息
     *
     * @throws CouponNotFoundException
     */
    public function detectCoupon(string $couponId): Coupon
    {
        $event = new DetectCouponEvent();
        $event->setCouponId($couponId);
        $this->eventDispatcher->dispatch($event);
        if ($event->getCoupon()) {
            return $event->getCoupon();
        }

        $coupon = null;
        if (!$coupon) {
            $coupon = $this->couponRepository->findOneBy(['sn' => $couponId]);
        }
        if (!$coupon && is_numeric($couponId)) {
            $coupon = $this->couponRepository->findOneBy(['id' => $couponId]);
        }
        if (!$coupon) {
            throw new CouponNotFoundException('找不到优惠券');
        }

        return $coupon;
    }

    /**
     * 查询优惠券详情
     */
    public function getCouponValidStock(Coupon $coupon): int
    {
        return $this->codeRepository->count([
            'coupon' => $coupon,
            'gatherTime' => null,
            'valid' => true,
        ]);
    }

    /**
     * 创建一个唯一码
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createOneCode(Coupon $coupon): Code
    {
        $code = new Code();
        $code->setCoupon($coupon);
        $code->setSn($this->codeGen->generate());
        $code->setValid(true);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return $code;
    }

    /**
     * 选中某个优惠券码
     *
     * @param bool $renewable false表示无可领取券码时直接返回
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function pickCode(UserInterface $user, Coupon $coupon, bool $renewable = true): ?Code
    {
        // TODO: 重新实现条件检查逻辑
        // $this->conditionManager->checkRequirements($coupon, $user);

        $code = $this->codeRepository->createQueryBuilder('a')
            ->where('a.coupon = :coupon AND a.valid = true AND a.owner IS NULL AND a.gatherTime IS NULL')
            ->setParameter('coupon', $coupon)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$code) {
            if (!$renewable) {
                throw new PickCodeNotFoundException('找不到任何优惠券码');
            }
            $code = $this->createOneCode($coupon);
        }

        $code->setOwner($user);
        $code->setGatherTime(Carbon::now());

        // 过期天数
        if ($coupon->getExpireDay()) {
            $code->setExpireTime(Carbon::now()->addDays($coupon->getExpireDay()));
        }

        return $code;
    }

    /**
     * 发送优惠券
     */
    public function sendCode(UserInterface $user, Coupon $coupon, string $extend = ''): Code
    {
        $event = new SendCodeEvent();
        $event->setUser($user);
        $event->setCoupon($coupon);
        $event->setExtend($extend);
        $this->eventDispatcher->dispatch($event);
        if ($event->getCode()) {
            return $event->getCode();
        }

        $code = $this->pickCode($user, $coupon);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return $code;
    }

    /**
     * 获取优惠券码详情
     *
     * @throws CodeNotFoundException
     */
    public function getCodeDetail(UserInterface $user, string $sn): Code
    {
        $code = $this->codeRepository->findOneBy([
            'owner' => $user,
            'sn' => $sn,
        ]);
        if (!$code) {
            $event = new CodeNotFoundEvent();
            $event->setSn($sn);
            $event->setUser($user);
            $this->eventDispatcher->dispatch($event);
            if ($event->getCode()) {
                $code = $event->getCode();
            } else {
                $exception = new CodeNotFoundException();
                $exception->setSn($sn);
                throw $exception;
            }
        }

        return $code;
    }

    /**
     * 标记某个优惠券为无效状态
     */
    public function markAsInvalid(Code $code): void
    {
        $code->setValid(false);
        $code->setLocked(false);
        $this->entityManager->persist($code);
        $this->entityManager->flush();
    }

    /**
     * 锁定优惠券码
     */
    public function lockCode(Code $code): void
    {
        if ($code->getUseTime()) {
            throw new CodeUsedException();
        }

        $code->setLocked(true);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $event = new CodeLockedEvent();
        $event->setCode($code);
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * 解锁优惠券码
     */
    public function unlockCode(Code $code): void
    {
        if ($code->getUseTime()) {
            throw new CodeUsedException();
        }

        $code->setLocked(false);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $event = new CodeUnlockEvent();
        $event->setCode($code);
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * 核销指定优惠券
     */
    public function redeemCode(Code $code, ?object $extra = null): void
    {
        if ($code->getUseTime()) {
            throw new CodeUsedException();
        }

        $code->setUseTime(Carbon::now());
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $event = new CodeRedeemEvent();
        $event->setCode($code);
        $event->setExtra($extra);
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * 读取优惠券的二维码地址
     */
    public function getQrcodeUrl(Code $code): string
    {
        return $this->urlGenerator->generate('qr_code_generate', [
            'data' => $code->getSn(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * 更新总数量
     */
    public function updateTotalNumber(string $couponId, int $num): void
    {
        $stat = $this->getCouponStat($couponId);
        $this->couponStatRepository->createQueryBuilder('a')
            ->update()
            ->set('a.totalNum', "a.totalNum + {$num}")
            ->where('a.couponId = :couponId')
            ->setParameter('couponId', $couponId)
            ->getQuery()
            ->execute();
    }

    /**
     * 更新使用数量
     */
    public function updateUsedNumber(string $couponId, int $num): void
    {
        $stat = $this->getCouponStat($couponId);
        $this->entityManager->refresh($stat);
        if ($stat->getTotalNum() <= 0) {
            return;
        }
        $this->couponStatRepository->createQueryBuilder('a')
            ->update()
            ->set('a.usedNum', "a.usedNum + {$num}")
            ->where('a.couponId = :couponId')
            ->setParameter('couponId', $couponId)
            ->getQuery()
            ->execute();
    }

    /**
     * 更新领取数量
     */
    public function updateReceivedNumber(string $couponId, int $num): void
    {
        $stat = $this->getCouponStat($couponId);
        $this->entityManager->refresh($stat);
        if ($stat->getTotalNum() <= 0) {
            return;
        }
        $this->couponStatRepository->createQueryBuilder('a')
            ->update()
            ->set('a.receivedNum', "a.receivedNum + {$num}")
            ->where('a.couponId = :couponId')
            ->setParameter('couponId', $couponId)
            ->getQuery()
            ->execute();
    }

    /**
     * 更新过期数量
     */
    public function updateExpiredNumber(string $couponId, int $num): void
    {
        $stat = $this->getCouponStat($couponId);
        $this->entityManager->refresh($stat);
        if ($stat->getTotalNum() <= 0) {
            return;
        }
        $this->couponStatRepository->createQueryBuilder('a')
            ->update()
            ->set('a.expiredNum', "a.expiredNum + {$num}")
            ->where('a.couponId = :couponId')
            ->setParameter('couponId', $couponId)
            ->getQuery()
            ->execute();
    }

    private function getCouponStat(string $couponId): CouponStat
    {
        $stat = $this->couponStatRepository->findOneBy(['couponId' => $couponId]);
        if (!$stat) {
            $stat = new CouponStat();
            $stat->setCouponId($couponId);
            $stat->setTotalNum(0);
            $stat->setUsedNum(0);
            $stat->setReceivedNum(0);
            $stat->setExpiredNum(0);
            $this->entityManager->persist($stat);
            $this->entityManager->flush();
        }

        return $stat;
    }
}
