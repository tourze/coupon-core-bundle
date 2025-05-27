<?php

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\OrderAmountCondition;

/**
 * 订单金额条件仓储类
 */
class OrderAmountConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderAmountCondition::class);
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
     * 根据金额范围查找条件
     */
    public function findByAmountRange(string $minAmount, ?string $maxAmount = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.minAmount <= :minAmount')
            ->setParameter('minAmount', $minAmount);

        if ($maxAmount !== null) {
            $qb->andWhere('c.maxAmount IS NULL OR c.maxAmount >= :maxAmount')
               ->setParameter('maxAmount', $maxAmount);
        }

        return $qb->getQuery()->getResult();
    }
} 