<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Repository\CouponUsageLogRepository;

/**
 * @internal
 */
#[CoversClass(CouponUsageLogRepository::class)]
final class CouponUsageLogRepositoryTest extends TestCase
{
    public function testExtendsBaseRepository(): void
    {
        $stub = $this->getMockBuilder(CouponUsageLogRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        self::assertInstanceOf(ServiceEntityRepository::class, $stub);
    }
}
