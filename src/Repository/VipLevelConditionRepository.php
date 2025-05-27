<?php

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\VipLevelCondition;

/**
 * VIP等级条件仓储类
 */
class VipLevelConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VipLevelCondition::class);
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

    /**
     * 根据VIP等级范围查找条件
     */
    public function findByLevelRange(int $minLevel, ?int $maxLevel = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.minLevel <= :minLevel')
            ->setParameter('minLevel', $minLevel);

        if ($maxLevel !== null) {
            $qb->andWhere('c.maxLevel IS NULL OR c.maxLevel >= :maxLevel')
               ->setParameter('maxLevel', $maxLevel);
        }

        return $qb->getQuery()->getResult();
    }
} 