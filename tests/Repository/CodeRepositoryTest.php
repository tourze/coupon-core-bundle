<?php

namespace Tourze\CouponCoreBundle\Tests\Repository;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(CodeRepository::class)]
#[RunTestsInSeparateProcesses]
final class CodeRepositoryTest extends AbstractRepositoryTestCase
{
    public function testRepositoryCanBeInstantiated(): void
    {
        $repository = self::getService(CodeRepository::class);
        $this->assertInstanceOf(CodeRepository::class, $repository);
    }

    public function testCreateUserCouponCodesQueryBuilder(): void
    {
        $repository = self::getService(CodeRepository::class);
        // 使用 UserManagerInterface 创建真实用户
        // 理由 1: 与项目其他测试保持一致（如 testSaveAndRemove、testFindByOwner 等）
        // 理由 2: 使用真实用户实体能更准确地测试 Repository 行为
        // 理由 3: 符合项目统一使用 UserManagerInterface 的规范
        $user = $this->createDoctrineTestUser('test-query-builder@example.com', 'password123');

        // 测试基本用法（无筛选条件）
        $queryBuilder = $repository->createUserCouponCodesQueryBuilder($user);
        $this->assertInstanceOf(QueryBuilder::class, $queryBuilder);

        // 测试带优惠券筛选
        // 使用直接实例化替代Mock的理由：
        // 理由 1: Coupon 是内部实体类，不应该被Mock
        // 理由 2: 直接实例化能更真实地测试Repository的行为
        // 理由 3: 满足静态分析要求，提高代码质量
        $coupon1 = new Coupon();
        $coupon2 = new Coupon();
        $queryBuilder = $repository->createUserCouponCodesQueryBuilder($user, [$coupon1, $coupon2]);
        $this->assertInstanceOf(QueryBuilder::class, $queryBuilder);

        // 测试状态筛选：待使用
        $queryBuilder = $repository->createUserCouponCodesQueryBuilder($user, [], 1);
        $this->assertInstanceOf(QueryBuilder::class, $queryBuilder);

        // 测试状态筛选：已使用
        $queryBuilder = $repository->createUserCouponCodesQueryBuilder($user, [], 2);
        $this->assertInstanceOf(QueryBuilder::class, $queryBuilder);

        // 测试状态筛选：已过期
        $queryBuilder = $repository->createUserCouponCodesQueryBuilder($user, [], 3);
        $this->assertInstanceOf(QueryBuilder::class, $queryBuilder);
    }

    protected function onSetUp(): void
    {
        // Repository 测试的设置逻辑已由父类 AbstractRepositoryTestCase 处理
        // 这里不需要额外的设置，因为所有必需的依赖都已通过继承获得
    }

    /**
     * 声明测试需要的接口实体映射
     *
     * 由于 Code 实体的 owner 属性关联到 UserInterface，
     * 需要告诉测试框架生成对应的测试实体类，
     * 以确保 Doctrine 能正确处理关联关系。
     *
     * @return array<class-string>
     */
    protected function getRequiredInterfaces(): array
    {
        return [\Symfony\Component\Security\Core\User\UserInterface::class];
    }

    /**
     * 创建可被Doctrine持久化的测试用户实体
     *
     * 由于框架中的 createNormalUser 返回的是 InMemoryUser，
     * 而 Code 实体需要真正的 Doctrine 实体，所以需要这个辅助方法。
     */
    private function createDoctrineTestUser(string $username, string $password): \Symfony\Component\Security\Core\User\UserInterface
    {
        // 查找已存在的用户或创建新用户
        $em = self::getEntityManager();

        // 检查是否存在动态生成的用户实体类
        $userEntityClass = null;
        foreach (get_declared_classes() as $class) {
            if (str_contains($class, 'TestUserInterface') && str_contains($class, 'DoctrineResolveTargetForTest')) {
                $userEntityClass = $class;
                break;
            }
        }

        if (null === $userEntityClass) {
            // 如果没有找到动态生成的实体类，回退到InMemoryUser
            // 虽然会导致测试失败，但至少能提供清晰的错误信息
            return $this->createNormalUser($username, $password);
        }

        // 创建实体实例
        $user = new $userEntityClass();

        // 设置基本属性
        if (method_exists($user, 'setUserIdentifier')) {
            $user->setUserIdentifier($username);
        }
        if (method_exists($user, 'setPassword')) {
            $user->setPassword($password);
        }
        if (method_exists($user, 'setRoles')) {
            $user->setRoles(['ROLE_USER']);
        }

        // 持久化用户
        $em->persist($user);
        $em->flush();

        self::assertInstanceOf(\Symfony\Component\Security\Core\User\UserInterface::class, $user);
        return $user;
    }

    protected function getRepository(): CodeRepository
    {
        return self::getService(CodeRepository::class);
    }

    protected function createNewEntity(): object
    {
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);

        $user = $this->createDoctrineTestUser('test@example.com', 'password123');

        $code = new Code();
        $code->setCoupon($coupon);
        $code->setOwner($user);
        $code->setSn('TEST_CODE_' . uniqid());
        $code->setValid(true);

        // 只持久化 coupon，因为 createNormalUser 已经持久化了用户
        $em = self::getEntityManager();
        $em->persist($coupon);

        return $code;
    }

    public function testSaveAndRemove(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建一个优惠券
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $em->persist($coupon);

        // 创建一个用户
        $user = $this->createDoctrineTestUser('test@example.com', 'password123');

        // 创建新的Code实体
        $code = new Code();
        $code->setCoupon($coupon);
        $code->setOwner($user);
        $code->setSn('TEST_CODE_' . uniqid());
        $code->setValid(true);

        // 测试保存
        $em->persist($code);
        $em->flush();
        $id = $code->getId();
        $this->assertGreaterThan(0, $id);

        // 清理实体管理器缓存，确保从数据库重新加载
        $em->clear();

        // 验证可以从数据库中检索
        $found = $repository->find($id);
        $this->assertInstanceOf(Code::class, $found);
        $this->assertEquals($code->getSn(), $found->getSn());
        $this->assertTrue($found->isValid());

        // 测试删除 - 重新从数据库加载实体
        $codeForDelete = $repository->find($id);
        if (null !== $codeForDelete) {
            $em->remove($codeForDelete);
            $em->flush();
        }
        $em->clear();
        $found = $repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByCoupon(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建一个优惠券
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $em->persist($coupon);

        // 创建一个用户
        $user = $this->createDoctrineTestUser('test@example.com', 'password123');

        // 创建多个Code实体
        $code1 = new Code();
        $code1->setCoupon($coupon);
        $code1->setOwner($user);
        $code1->setSn('TEST_CODE_1_' . uniqid());
        $code1->setValid(true);

        $code2 = new Code();
        $code2->setCoupon($coupon);
        $code2->setOwner($user);
        $code2->setSn('TEST_CODE_2_' . uniqid());
        $code2->setValid(true);

        $em->persist($code1);
        $em->persist($code2);
        $em->flush();

        // 测试按优惠券查找
        $results = $repository->findBy(['coupon' => $coupon]);
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // 验证结果中的Code实体
        $codes = array_map(fn ($code) => $code->getSn(), $results);
        $this->assertContains($code1->getSn(), $codes);
        $this->assertContains($code2->getSn(), $codes);
    }

    public function testFindByOwner(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建一个优惠券
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $em->persist($coupon);

        // 创建一个用户
        $user = $this->createDoctrineTestUser('test@example.com', 'password123');

        // 创建Code实体
        $code = new Code();
        $code->setCoupon($coupon);
        $code->setOwner($user);
        $code->setSn('TEST_CODE_OWNER_' . uniqid());
        $code->setValid(true);

        $em->persist($code);
        $em->flush();

        // 测试按拥有者查找
        $results = $repository->findBy(['owner' => $user]);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));

        // 验证找到的Code属于指定用户
        $found = false;
        foreach ($results as $result) {
            if ($result->getSn() === $code->getSn()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '应该找到创建的Code');
    }

    public function testCountByCoupon(): void
    {
        $repository = $this->getRepository();
        $em = self::getEntityManager();

        // 创建一个优惠券
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setValid(true);
        $coupon->setExpireDay(30);
        $em->persist($coupon);

        // 创建一个用户
        $user = $this->createDoctrineTestUser('test@example.com', 'password123');

        // 创建Code实体
        $code = new Code();
        $code->setCoupon($coupon);
        $code->setOwner($user);
        $code->setSn('TEST_CODE_COUNT_' . uniqid());
        $code->setValid(true);

        $em->persist($code);
        $em->flush();

        // 测试按优惠券计数
        $count = $repository->count(['coupon' => $coupon]);
        $this->assertEquals(1, $count);
    }
}
