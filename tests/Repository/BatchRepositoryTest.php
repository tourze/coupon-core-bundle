<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Batch;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\BatchRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(BatchRepository::class)]
#[RunTestsInSeparateProcesses]
final class BatchRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository test setup
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $em->persist($coupon);
        $em->flush();

        $batch = new Batch();
        $batch->setCoupon($coupon);
        $batch->setTotalNum(100);
        $batch->setSendNum(0);
        $batch->setRemark('测试批次');

        $em->persist($batch);
        $em->flush();

        $this->assertNotNull($batch->getId());
        $found = $repository->find($batch->getId());
        $this->assertInstanceOf(Batch::class, $found);
        $this->assertEquals(100, $found->getTotalNum());
        $this->assertEquals(0, $found->getSendNum());
        $this->assertEquals('测试批次', $found->getRemark());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $em->persist($coupon);
        $em->flush();

        $batch = new Batch();
        $batch->setCoupon($coupon);
        $batch->setTotalNum(50);
        $batch->setSendNum(0);
        $em->persist($batch);
        $em->flush();

        $batchId = $batch->getId();
        $em->remove($batch);
        $em->flush();

        $found = $repository->find($batchId);
        $this->assertNull($found);
    }

    public function testFindWithNonExistingIdShouldReturnNull(): void
    {
        $repository = $this->getRepository();
        $found = $repository->find(999999);
        $this->assertNull($found);
    }

    public function testFindOneByWithNonMatchingCriteria(): void
    {
        $repository = $this->getRepository();
        $found = $repository->findOneBy(['totalNum' => 999999]);
        $this->assertNull($found);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon = new Coupon();
        $coupon->setName('排序测试优惠券');
        $em->persist($coupon);
        $em->flush();

        $batch1 = new Batch();
        $batch1->setCoupon($coupon);
        $batch1->setTotalNum(9999);
        $batch1->setSendNum(10);

        $batch2 = new Batch();
        $batch2->setCoupon($coupon);
        $batch2->setTotalNum(9998);
        $batch2->setSendNum(20);

        $em->persist($batch1);
        $em->persist($batch2);
        $em->flush();

        $found = $repository->findOneBy([], ['totalNum' => 'DESC']);
        $this->assertInstanceOf(Batch::class, $found);
        $this->assertEquals(9999, $found->getTotalNum());
    }

    public function testFindByCouponAssociation(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon1 = new Coupon();
        $coupon1->setName('优惠券1');
        $coupon2 = new Coupon();
        $coupon2->setName('优惠券2');
        $em->persist($coupon1);
        $em->persist($coupon2);
        $em->flush();

        $batch1 = new Batch();
        $batch1->setCoupon($coupon1);
        $batch1->setTotalNum(100);
        $batch1->setSendNum(10);

        $batch2 = new Batch();
        $batch2->setCoupon($coupon2);
        $batch2->setTotalNum(200);
        $batch2->setSendNum(20);

        $em->persist($batch1);
        $em->persist($batch2);
        $em->flush();

        $results = $repository->findBy(['coupon' => $coupon1]);
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals(100, $results[0]->getTotalNum());
    }

    public function testCountByCouponAssociation(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $em->persist($coupon);
        $em->flush();

        $batch = new Batch();
        $batch->setCoupon($coupon);
        $batch->setTotalNum(100);
        $batch->setSendNum(0);
        $em->persist($batch);
        $em->flush();

        $count = $repository->count(['coupon' => $coupon]);
        $this->assertEquals(1, $count);
    }

    public function testFindByNullRemark(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $em->persist($coupon);
        $em->flush();

        $batch = new Batch();
        $batch->setCoupon($coupon);
        $batch->setTotalNum(100);
        $batch->setSendNum(0);
        $batch->setRemark(null);
        $em->persist($batch);
        $em->flush();

        $results = $repository->findBy(['remark' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testCountByNullRemark(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $em->persist($coupon);
        $em->flush();

        $batch = new Batch();
        $batch->setCoupon($coupon);
        $batch->setTotalNum(100);
        $batch->setSendNum(0);
        $batch->setRemark(null);
        $em->persist($batch);
        $em->flush();

        $count = $repository->count(['remark' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    protected function getRepository(): BatchRepository
    {
        return self::getService(BatchRepository::class);
    }

    protected function createNewEntity(): object
    {
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);

        $batch = new Batch();
        $batch->setCoupon($coupon);
        $batch->setTotalNum(100);
        $batch->setSendNum(0);
        $batch->setRemark('测试批次');

        return $batch;
    }
}
