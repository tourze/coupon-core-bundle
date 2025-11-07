<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\CouponAllocationDetail;

/**
 * @extends ServiceEntityRepository<CouponAllocationDetail>
 */
class CouponAllocationDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CouponAllocationDetail::class);
    }
}
