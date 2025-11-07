<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Repository\CouponAllocationDetailRepository;

/**
 * @internal
 */
#[CoversClass(CouponAllocationDetailRepository::class)]
final class CouponAllocationDetailRepositoryTest extends TestCase
{
    public function testExtendsBaseRepository(): void
    {
        $stub = $this->getMockBuilder(CouponAllocationDetailRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        self::assertInstanceOf(ServiceEntityRepository::class, $stub);
    }
}
