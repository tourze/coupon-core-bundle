<?php

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\CouponStat;


/**
 * @method CouponStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method CouponStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method CouponStat[]    findAll()
 * @method CouponStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponStatRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CouponStat::class);
    }
}
