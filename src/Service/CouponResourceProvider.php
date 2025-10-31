<?php

namespace Tourze\CouponCoreBundle\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;
use Tourze\ResourceManageBundle\Service\ResourceProvider;

/**
 * 优惠券发奖逻辑
 */
class CouponResourceProvider implements ResourceProvider
{
    public const CODE = 'coupon';

    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CouponService $couponService,
    ) {
    }

    public function getCode(): string
    {
        return self::CODE;
    }

    public function getLabel(): string
    {
        return '优惠券';
    }

    /**
     * @return array<int, Coupon>|null
     */
    public function getIdentities(): ?iterable
    {
        return $this->couponRepository->findBy(['valid' => true]);
    }

    public function findIdentity(string $identity): ?ResourceIdentity
    {
        return $this->couponService->detectCoupon($identity);
    }

    public function sendResource(UserInterface $user, ResourceIdentity|Coupon|null $identity, string $amount, int|float|null $expireDay = null, ?\DateTimeInterface $expireTime = null): void
    {
        if (!$identity instanceof Coupon) {
            throw new CouponNotFoundException('找不到要发送的优惠券');
        }
        $this->couponService->sendCode($user, $identity);
    }
}
