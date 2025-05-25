<?php

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\Satisfy;


/**
 * @method Satisfy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Satisfy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Satisfy[]    findAll()
 * @method Satisfy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SatisfyRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Satisfy::class);
    }
}
