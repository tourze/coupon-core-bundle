<?php

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\GatherCountLimitCondition;

/**
 * 领取次数限制条件仓储类
 */
class GatherCountLimitConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GatherCountLimitCondition::class);
    }

    /**
     * 根据优惠券查找条件
     */
    public function findByCoupon(Coupon $coupon): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.coupon = :coupon')
            ->setParameter('coupon', $coupon)
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据优惠券查找启用的条件
     */
    public function findEnabledByCoupon(Coupon $coupon): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.coupon = :coupon')
            ->andWhere('c.enabled = :enabled')
            ->setParameter('coupon', $coupon)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
    }
} 