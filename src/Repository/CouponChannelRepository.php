<?php

namespace Tourze\CouponCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCoreBundle\Entity\CouponChannel;


/**
 * @method CouponChannel|null find($id, $lockMode = null, $lockVersion = null)
 * @method CouponChannel|null findOneBy(array $criteria, array $orderBy = null)
 * @method CouponChannel[]    findAll()
 * @method CouponChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponChannelRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CouponChannel::class);
    }
}
