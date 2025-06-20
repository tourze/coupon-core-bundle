<?php

namespace Tourze\CouponCoreBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Repository\CodeRepository;

/**
 * 自动切换优惠券码的状态
 */
#[AsCommand(name: self::NAME, description: '自动回收过期优惠券')]
class RevokeExpiredCodeCommand extends Command
{
    public const NAME = 'coupon:revoke-expired-code';
    /**
     * @var int 每分钟处理数量
     */
    protected int $number = 500;

    public function __construct(
        private readonly CodeRepository $codeRepository, 
        private readonly EntityManagerInterface $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $codes = $this->codeRepository->createQueryBuilder('a')
            ->where('a.useTime IS NULL') // 未使用过
            ->andWhere('a.expireTime IS NOT NULL') // 有过期时间
            ->andWhere('a.expireTime < :time') // 已经过了过期时间
            ->andWhere('a.valid = true') // 但是还标记为有效
            ->setParameter('time', CarbonImmutable::now()->format('Y-m-d H:i:s'))
            ->orderBy('a.id', Criteria::DESC)
            ->setMaxResults($this->number)
            ->getQuery()
            ->toIterable();
        foreach ($codes as $code) {
            /* @var Code $code */
            $code->setValid(false);
            $this->entityManager->persist($code);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
