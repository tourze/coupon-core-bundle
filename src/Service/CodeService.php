<?php

namespace Tourze\CouponCoreBundle\Service;

use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

class CodeService
{
    public function __construct(private readonly CodeRepository $codeRepository)
    {
    }

    /**
     * 获取可用库存
     */
    public function getValidStock(Coupon $coupon): int
    {
        return (int) $this->codeRepository->createQueryBuilder('a')
            ->where('a.owner IS NULL')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 获取已领取库存
     */
    public function getGatherStock(Coupon $coupon): int
    {
        return (int) $this->codeRepository->createQueryBuilder('a')
            ->where('a.owner IS NOT NULL')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
