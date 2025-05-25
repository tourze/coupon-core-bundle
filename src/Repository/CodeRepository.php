<?php

namespace Tourze\CouponCoreBundle\Repository;

use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;

/**
 * @method Code|null find($id, $lockMode = null, $lockVersion = null)
 * @method Code|null findOneBy(array $criteria, array $orderBy = null)
 * @method Code[]    findAll()
 * @method Code[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Code::class);
    }

    /**
     * 获取用户优惠券码列表的查询构建器
     *
     * @param UserInterface $user    用户
     * @param array<Coupon> $coupons 指定优惠券列表
     * @param int           $status  状态：1待使用、2已使用、3已过期
     */
    public function createUserCouponCodesQueryBuilder(UserInterface $user, array $coupons = [], int $status = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.useTime', Criteria::ASC) // 未使用排在最前面
            ->addOrderBy('a.gatherTime', Criteria::DESC) // 最近领取的排前面
            ->addOrderBy('a.expireTime', Criteria::DESC); // 已使用已过期排在后面，两者不管顺序

        // 查询指定用户的优惠券
        $qb->where('a.owner = :user')
            ->setParameter('user', $user);

        // 按指定优惠券筛选
        if (!empty($coupons)) {
            $qb->andWhere('a.coupon IN (:coupons)')
                ->setParameter('coupons', $coupons);
        }

        // 按状态筛选
        switch ($status) {
            case 1: // 待使用
                $qb->andWhere('a.useTime IS NULL')
                    ->orderBy('a.gatherTime', Criteria::DESC);
                break;
            case 2: // 已使用
                $qb->andWhere('a.useTime IS NOT NULL')
                    ->orderBy('a.useTime', Criteria::DESC);
                break;
            case 3: // 已过期
                $qb->andWhere('a.useTime IS NULL AND a.expireTime < :now')
                    ->setParameter('now', Carbon::now())
                    ->orderBy('a.expireTime', Criteria::DESC);
                break;
        }

        return $qb;
    }
}
