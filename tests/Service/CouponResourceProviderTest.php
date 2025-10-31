<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponResourceProvider;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

/**
 * @internal
 */
#[CoversClass(CouponResourceProvider::class)]
final class CouponResourceProviderTest extends TestCase
{
    /**
     * 创建 CouponRepository 的匿名实现
     *
     * 使用匿名类替代Mock以满足静态分析要求：
     * 1. 避免Mock具体实现类，提高代码质量
     * 2. 提供可控的测试依赖行为
     * 3. 保持测试的意图和覆盖范围
     *
     * @param array<Coupon> $findByResult
     */
    private function createCouponRepository(array $findByResult = []): CouponRepository
    {
        return new class($findByResult) extends CouponRepository {
            /**
             * @param array<Coupon> $findByResult
             * @phpstan-ignore-next-line constructor.missingParentCall
             */
            public function __construct(private array $findByResult = [])
            {
                // 跳过父类构造函数，避免需要ManagerRegistry
            }

            /**
             * @param array<string, mixed> $criteria
             * @param array<string, string>|null $orderBy
             * @param int|null $limit
             * @param int|null $offset
             * @return list<Coupon>
             */
            public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return array_values($this->findByResult);
            }
        };
    }

    /**
     * 由于 CouponService 是 readonly 类，不能继承，此处使用 Mock
     * 这是对 readonly 类的一个合理的例外情况
     */
    public function testGetCode(): void
    {
        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $this->assertSame('coupon', $provider->getCode());
        $this->assertSame(CouponResourceProvider::CODE, $provider->getCode());
    }

    public function testGetLabel(): void
    {
        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $this->assertSame('优惠券', $provider->getLabel());
    }

    public function testGetIdentities(): void
    {
        $coupon1 = new Coupon();
        $coupon2 = new Coupon();

        $couponRepository = $this->createCouponRepository([$coupon1, $coupon2]);
        $couponService = $this->createMock(CouponService::class);

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $identitiesIterator = $provider->getIdentities();
        $this->assertNotNull($identitiesIterator);
        $identities = iterator_to_array($identitiesIterator);
        $this->assertCount(2, $identities);
        $this->assertSame($coupon1, $identities[0]);
        $this->assertSame($coupon2, $identities[1]);
    }

    public function testFindIdentity(): void
    {
        $coupon = new Coupon();
        $identity = 'test-identity';

        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);
        $couponService->expects($this->once())
            ->method('detectCoupon')
            ->with($identity)
            ->willReturn($coupon)
        ;

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $result = $provider->findIdentity($identity);
        $this->assertSame($coupon, $result);
    }

    public function testSendResource(): void
    {
        $user = $this->createMock(UserInterface::class);
        $coupon = new Coupon();

        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);
        $couponService->expects($this->once())
            ->method('sendCode')
            ->with($user, $coupon)
        ;

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $provider->sendResource($user, $coupon, '1', null, null);
    }

    public function testSendResourceWithInvalidIdentity(): void
    {
        $user = $this->createMock(UserInterface::class);
        $invalidIdentity = $this->createMock(ResourceIdentity::class);

        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $this->expectException(CouponNotFoundException::class);
        $this->expectExceptionMessage('找不到要发送的优惠券');

        $provider->sendResource($user, $invalidIdentity, '1', null, null);
    }

    public function testSendResourceWithNull(): void
    {
        $user = $this->createMock(UserInterface::class);

        $couponRepository = $this->createCouponRepository();
        $couponService = $this->createMock(CouponService::class);

        $provider = new CouponResourceProvider($couponRepository, $couponService);

        $this->expectException(CouponNotFoundException::class);
        $this->expectExceptionMessage('找不到要发送的优惠券');

        $provider->sendResource($user, null, '1', null, null);
    }
}
