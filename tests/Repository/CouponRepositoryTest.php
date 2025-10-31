<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(CouponRepository::class)]
#[RunTestsInSeparateProcesses]
final class CouponRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository test setup
    }

    public function testRepositoryCanBeInstantiated(): void
    {
        $repository = self::getService(CouponRepository::class);
        $this->assertInstanceOf(CouponRepository::class, $repository);
    }

    public function testSaveAndRemove(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建新的Coupon实体
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $coupon->setSn('TEST_COUPON_' . uniqid());

        // 测试保存
        $em->persist($coupon);
        $em->flush();
        $id = $coupon->getId();
        $this->assertGreaterThan(0, $id);

        // 清理实体管理器缓存，确保从数据库重新加载
        $em->clear();

        // 验证可以从数据库中检索
        $found = $repository->find($id);
        $this->assertInstanceOf(Coupon::class, $found);
        $this->assertEquals($coupon->getName(), $found->getName());
        $this->assertEquals($coupon->getSn(), $found->getSn());
        $this->assertTrue($found->isValid());
        $this->assertEquals($coupon->getExpireDay(), $found->getExpireDay());

        // 测试删除 - 重新从数据库加载实体
        $couponForDelete = $repository->find($id);
        if (null !== $couponForDelete) {
            $em->remove($couponForDelete);
            $em->flush();
        }
        $em->clear();
        $found = $repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByValidCoupons(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建有效的优惠券
        $validCoupon = new Coupon();
        $validCoupon->setName('有效优惠券');
        $validCoupon->setValid(true);
        $validCoupon->setExpireDay(30);
        $validCoupon->setSn('VALID_COUPON_' . uniqid());

        // 创建无效的优惠券
        $invalidCoupon = new Coupon();
        $invalidCoupon->setName('无效优惠券');
        $invalidCoupon->setValid(false);
        $invalidCoupon->setExpireDay(30);
        $invalidCoupon->setSn('INVALID_COUPON_' . uniqid());

        $em->persist($validCoupon);
        $em->persist($invalidCoupon);
        $em->flush();

        // 测试按有效状态查找
        $results = $repository->findBy(['valid' => true]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));

        // 验证找到的都是有效的优惠券
        $foundValid = false;
        foreach ($results as $result) {
            if ($result->getSn() === $validCoupon->getSn()) {
                $foundValid = true;
                break;
            }
        }
        $this->assertTrue($foundValid, '应该找到有效的优惠券');
    }

    public function testFindByExpireDay(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建指定有效期的优惠券
        $coupon = new Coupon();
        $coupon->setName('30天优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $coupon->setSn('EXPIRE_COUPON_' . uniqid());

        $em->persist($coupon);
        $em->flush();

        // 测试按有效期查找
        $results = $repository->findBy(['expireDay' => 30]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));

        // 验证找到的优惠券
        $found = false;
        foreach ($results as $result) {
            if ($result->getSn() === $coupon->getSn()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '应该找到30天有效期的优惠券');
    }

    public function testCountByValidCoupons(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建有效的优惠券
        $validCoupon = new Coupon();
        $validCoupon->setName('有效优惠券');
        $validCoupon->setValid(true);
        $validCoupon->setExpireDay(30);
        $validCoupon->setSn('COUNT_VALID_COUPON_' . uniqid());

        $em->persist($validCoupon);
        $em->flush();

        // 测试按有效状态计数
        $count = $repository->count(['valid' => true]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneBySn(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建优惠券
        $coupon = new Coupon();
        $uniqueSn = 'UNIQUE_SN_' . uniqid();
        $coupon->setName('唯一SN优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $coupon->setSn($uniqueSn);

        $em->persist($coupon);
        $em->flush();

        // 测试按序列号查找单个优惠券
        $found = $repository->findOneBy(['sn' => $uniqueSn]);
        $this->assertInstanceOf(Coupon::class, $found);
        $this->assertEquals($uniqueSn, $found->getSn());
        $this->assertEquals('唯一SN优惠券', $found->getName());
    }

    /**
     * @return ServiceEntityRepository<Coupon>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(CouponRepository::class);
    }

    protected function createNewEntity(): object
    {
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $coupon->setSn('TEST_COUPON_' . uniqid());

        return $coupon;
    }
}
