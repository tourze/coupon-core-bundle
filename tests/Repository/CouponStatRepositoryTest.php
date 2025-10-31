<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\CouponStat;
use Tourze\CouponCoreBundle\Repository\CouponStatRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(CouponStatRepository::class)]
#[RunTestsInSeparateProcesses]
final class CouponStatRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository test setup
    }

    public function testRepositoryCanBeInstantiated(): void
    {
        $repository = self::getService(CouponStatRepository::class);
        $this->assertInstanceOf(CouponStatRepository::class, $repository);
    }

    public function testSaveAndRemove(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建新的CouponStat实体
        $couponStat = new CouponStat();
        $couponStat->setCouponId('TEST_COUPON_' . uniqid());
        $couponStat->setTotalNum(100);
        $couponStat->setReceivedNum(50);
        $couponStat->setUsedNum(25);
        $couponStat->setExpiredNum(10);

        // 测试保存
        $em->persist($couponStat);
        $em->flush();
        $id = $couponStat->getId();
        $this->assertGreaterThan(0, $id);

        // 清理实体管理器缓存，确保从数据库重新加载
        $em->clear();

        // 验证可以从数据库中检索
        $found = $repository->find($id);
        $this->assertInstanceOf(CouponStat::class, $found);
        $this->assertEquals($couponStat->getCouponId(), $found->getCouponId());
        $this->assertEquals($couponStat->getTotalNum(), $found->getTotalNum());
        $this->assertEquals($couponStat->getReceivedNum(), $found->getReceivedNum());
        $this->assertEquals($couponStat->getUsedNum(), $found->getUsedNum());
        $this->assertEquals($couponStat->getExpiredNum(), $found->getExpiredNum());

        // 测试删除 - 重新从数据库加载实体
        $statForDelete = $repository->find($id);
        if (null !== $statForDelete) {
            $em->remove($statForDelete);
            $em->flush();
        }
        $em->clear();
        $found = $repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByCouponId(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建CouponStat实体
        $couponStat1 = new CouponStat();
        $couponId1 = 'COUPON_ID_1_' . uniqid();
        $couponStat1->setCouponId($couponId1);
        $couponStat1->setTotalNum(100);
        $couponStat1->setReceivedNum(50);
        $couponStat1->setUsedNum(25);
        $couponStat1->setExpiredNum(10);

        $couponStat2 = new CouponStat();
        $couponId2 = 'COUPON_ID_2_' . uniqid();
        $couponStat2->setCouponId($couponId2);
        $couponStat2->setTotalNum(200);
        $couponStat2->setReceivedNum(100);
        $couponStat2->setUsedNum(50);
        $couponStat2->setExpiredNum(20);

        $em->persist($couponStat1);
        $em->persist($couponStat2);
        $em->flush();

        // 测试按优惠券ID查找
        $results = $repository->findBy(['couponId' => $couponId1]);
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals($couponId1, $results[0]->getCouponId());
        $this->assertEquals(100, $results[0]->getTotalNum());
    }

    public function testFindByTotalNum(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建CouponStat实体
        $couponStat = new CouponStat();
        $couponStat->setCouponId('TOTAL_NUM_TEST_' . uniqid());
        $couponStat->setTotalNum(500);
        $couponStat->setReceivedNum(250);
        $couponStat->setUsedNum(125);
        $couponStat->setExpiredNum(50);

        $em->persist($couponStat);
        $em->flush();

        // 测试按总数量查找
        $results = $repository->findBy(['totalNum' => 500]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));

        // 验证找到的统计信息
        $found = false;
        foreach ($results as $result) {
            if ($result->getCouponId() === $couponStat->getCouponId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '应该找到创建的统计信息');
    }

    public function testCountByCouponId(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建CouponStat实体
        $couponStat = new CouponStat();
        $couponStat->setCouponId('COUNT_TEST_' . uniqid());
        $couponStat->setTotalNum(100);
        $couponStat->setReceivedNum(50);
        $couponStat->setUsedNum(25);
        $couponStat->setExpiredNum(10);

        $em->persist($couponStat);
        $em->flush();

        // 测试按优惠券ID计数
        $count = $repository->count(['couponId' => $couponStat->getCouponId()]);
        $this->assertEquals(1, $count);
    }

    public function testFindOneByCouponId(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建CouponStat实体
        $couponStat = new CouponStat();
        $uniqueCouponId = 'UNIQUE_COUPON_ID_' . uniqid();
        $couponStat->setCouponId($uniqueCouponId);
        $couponStat->setTotalNum(100);
        $couponStat->setReceivedNum(50);
        $couponStat->setUsedNum(25);
        $couponStat->setExpiredNum(10);

        $em->persist($couponStat);
        $em->flush();

        // 测试按优惠券ID查找单个统计信息
        $found = $repository->findOneBy(['couponId' => $uniqueCouponId]);
        $this->assertInstanceOf(CouponStat::class, $found);
        $this->assertEquals($uniqueCouponId, $found->getCouponId());
        $this->assertEquals(100, $found->getTotalNum());
        $this->assertEquals(50, $found->getReceivedNum());
        $this->assertEquals(25, $found->getUsedNum());
        $this->assertEquals(10, $found->getExpiredNum());
    }

    /**
     * @return ServiceEntityRepository<CouponStat>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(CouponStatRepository::class);
    }

    protected function createNewEntity(): object
    {
        $couponStat = new CouponStat();
        $couponStat->setCouponId('TEST_COUPON_' . uniqid());
        $couponStat->setTotalNum(100);
        $couponStat->setReceivedNum(50);
        $couponStat->setUsedNum(25);
        $couponStat->setExpiredNum(10);

        return $couponStat;
    }
}
