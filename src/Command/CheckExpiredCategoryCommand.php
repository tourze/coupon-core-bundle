<?php

namespace Tourze\CouponCoreBundle\Command;

use Carbon\CarbonImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

/**
 * 检查优惠券类别的有效期
 */
#[AsCronTask('* * * * *')]
#[AsCommand(name: self::NAME, description: '检查优惠券类别的有效期')]
class CheckExpiredCategoryCommand extends Command
{
    public const NAME = 'coupon:check-expired-category';
    public function __construct(private readonly CategoryRepository $categoryRepository, ?string $name = null)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->categoryRepository->createQueryBuilder('c')
            ->where('c.startTime > :now or c.endTime < :now')
            ->setParameter('now', CarbonImmutable::now())
            ->set('c.valid', 0)
            ->update()
            ->getQuery()
            ->execute();

        return Command::SUCCESS;
    }
}
