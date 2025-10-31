<?php

namespace Tourze\CouponCoreBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

#[Autoconfigure(public: true)]
readonly class CodeService
{
    public function __construct(private CodeRepository $codeRepository)
    {
    }

    /**
     * 获取可用库存
     *
     * 不考虑并发：只读统计查询，无需锁控制
     */
    public function getValidStock(Coupon $coupon): int
    {
        return (int) $this->codeRepository->createQueryBuilder('a')
            ->where('a.coupon = :coupon AND a.owner IS NULL')
            ->setParameter('coupon', $coupon)
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 获取已领取库存
     *
     * 不考虑并发：只读统计查询，无需锁控制
     */
    public function getGatherStock(Coupon $coupon): int
    {
        return (int) $this->codeRepository->createQueryBuilder('a')
            ->where('a.coupon = :coupon AND a.owner IS NOT NULL')
            ->setParameter('coupon', $coupon)
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
