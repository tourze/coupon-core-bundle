<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use Carbon\Carbon;
use CouponCode\CouponCode;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Entity\Requirement;
use Tourze\CouponCoreBundle\Enum\RequirementType;
use Tourze\CouponCoreBundle\Event\CodeLockedEvent;
use Tourze\CouponCoreBundle\Event\CodeRedeemEvent;
use Tourze\CouponCoreBundle\Event\CodeUnlockEvent;
use Tourze\CouponCoreBundle\Event\DetectCouponEvent;
use Tourze\CouponCoreBundle\Exception\CouponNotFoundException;
use Tourze\CouponCoreBundle\Exception\CouponRequirementException;
use Tourze\CouponCoreBundle\Exception\PickCodeNotFoundException;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Repository\CouponStatRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

/**
 * 自定义的用户接口，用于测试
 */
interface TestUserInterface extends UserInterface
{
    public function getCreateTime(): \DateTimeInterface;
}

/**
 * 用于测试的用户实现
 */
class TestUser implements \CouponCoreBundle\Tests\Service\TestUserInterface
{
    private Carbon $createTime;
    
    public function __construct(\DateTimeInterface $createTime)
    {
        // 确保我们使用 Carbon 实例
        $this->createTime = Carbon::instance($createTime);
    }
    
    public function getCreateTime(): \DateTimeInterface
    {
        return $this->createTime;
    }
    
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }
    
    public function eraseCredentials(): void
    {
        // 不做任何事
    }
    
    public function getUserIdentifier(): string
    {
        return 'test-user';
    }
}

class CouponServiceTest extends TestCase
{
    private CouponService $couponService;
    private CouponRepository $couponRepository;
    private CodeRepository $codeRepository;
    private CouponCode $codeGen;
    private EventDispatcherInterface $eventDispatcher;
    private UrlGeneratorInterface $urlGenerator;
    private CouponStatRepository $couponStatRepository;
    private EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->codeRepository = $this->createMock(CodeRepository::class);
        $this->codeGen = $this->createMock(CouponCode::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->couponStatRepository = $this->createMock(CouponStatRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->couponService = new CouponService(
            $this->couponRepository,
            $this->codeRepository,
            $this->codeGen,
            $this->eventDispatcher,
            $this->urlGenerator,
            $this->couponStatRepository,
            $this->entityManager
        );
    }
    
    public function testCreateOneCode(): void
    {
        $coupon = $this->createMock(Coupon::class);
        
        $this->codeGen->expects($this->once())
            ->method('generate')
            ->willReturn('CODE12345');
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($code) use ($coupon) {
                return $code instanceof Code
                    && $code->getCoupon() === $coupon
                    && $code->getSn() === 'CODE12345'
                    && $code->isValid() === true;
            }));
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->couponService->createOneCode($coupon);
        
        $this->assertInstanceOf(Code::class, $result);
        $this->assertSame($coupon, $result->getCoupon());
        $this->assertEquals('CODE12345', $result->getSn());
        $this->assertTrue($result->isValid());
    }
    
    public function testMarkAsInvalid(): void
    {
        $code = new Code();
        $code->setSn('CODE12345');
        $code->setValid(true);
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $this->couponService->markAsInvalid($code);
        
        $this->assertFalse($code->isValid());
    }
    
    public function testPickCodeWithExistingCode(): void
    {
        $user = new TestUser(new DateTime('-10 days'));
        
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('getRequirements')->willReturn(new ArrayCollection([]));
        $coupon->method('getExpireDay')->willReturn(30);
        
        $code = new Code();
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn($code);
        
        $result = $this->couponService->pickCode($user, $coupon);
        
        $this->assertSame($code, $result);
        $this->assertSame($user, $result->getOwner());
        $this->assertNotNull($result->getGatherTime());
        $this->assertNotNull($result->getExpireTime());
    }
    
    public function testPickCodeWithRenewal(): void
    {
        $user = new TestUser(new DateTime('-10 days'));
        
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('getRequirements')->willReturn(new ArrayCollection([]));
        $coupon->method('getExpireDay')->willReturn(30);
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        // 没有可用的代码
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);
        
        // 模拟创建新代码
        $this->codeGen->expects($this->once())
            ->method('generate')
            ->willReturn('NEW12345');
        
        $this->entityManager->expects($this->once())
            ->method('persist');
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->couponService->pickCode($user, $coupon);
        
        $this->assertInstanceOf(Code::class, $result);
        $this->assertSame($user, $result->getOwner());
        $this->assertNotNull($result->getGatherTime());
        $this->assertNotNull($result->getExpireTime());
    }
    
    public function testPickCodeNoRenewal(): void
    {
        $user = new TestUser(new DateTime('-10 days'));
        
        $coupon = $this->createMock(Coupon::class);
        $coupon->method('getRequirements')->willReturn(new ArrayCollection([]));
        
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->codeRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->willReturn($queryBuilder);
        
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        
        // 没有可用的代码
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);
        
        // 不允许自动创建新代码
        $this->expectException(PickCodeNotFoundException::class);
        $this->couponService->pickCode($user, $coupon, false);
    }
    
    public function testCheckCouponRequirementRegDayLt(): void
    {
        $user = new TestUser(new DateTime('-5 days')); // 注册5天，低于10天限制
        
        $coupon = $this->createMock(Coupon::class);
        
        $requirement = new Requirement();
        $requirement->setType(RequirementType::REG_DAY_LT);
        $requirement->setValue(10); // 需要注册时间小于10天
        
        $requirements = new ArrayCollection([$requirement]);
        $coupon->method('getRequirements')->willReturn($requirements);
        
        // 应该通过检查，不抛异常
        $result = $this->couponService->checkCouponRequirement($user, $coupon);
        $this->assertTrue($result);
    }
    
    public function testCheckCouponRequirementRegDayGt(): void
    {
        $user = new TestUser(new DateTime('-5 days'));
        
        $coupon = $this->createMock(Coupon::class);
        
        $requirement = new Requirement();
        $requirement->setType(RequirementType::REG_DAY_GT);
        $requirement->setValue(10); // 需要注册时间大于10天
        
        $requirements = new ArrayCollection([$requirement]);
        $coupon->method('getRequirements')->willReturn($requirements);
        
        $this->expectException(CouponRequirementException::class);
        $this->couponService->checkCouponRequirement($user, $coupon);
    }
    
    public function testCheckCouponRequirementTotalGatherCount(): void
    {
        $user = new TestUser(new DateTime('-10 days'));
        
        $coupon = $this->createMock(Coupon::class);
        
        $requirement = new Requirement();
        $requirement->setType(RequirementType::TOTAL_GATHER_COUNT);
        $requirement->setValue(3); // 最多领取3张
        
        $requirements = new ArrayCollection([$requirement]);
        $coupon->method('getRequirements')->willReturn($requirements);
        
        // 用户已经领取了4张，超过了限制
        $this->codeRepository->expects($this->once())
            ->method('count')
            ->with([
                'coupon' => $coupon,
                'owner' => $user,
            ])
            ->willReturn(4);
        
        $this->expectException(CouponRequirementException::class);
        $this->couponService->checkCouponRequirement($user, $coupon);
    }
    
    public function testCheckCouponRequirementSuccess(): void
    {
        // 使用模拟对象，避免真实的日期计算
        $user = $this->createMock(TestUserInterface::class);
        $coupon = $this->createMock(Coupon::class);
        
        // 使用空的要求集合，这样就不会有任何检查失败
        $coupon->method('getRequirements')->willReturn(new ArrayCollection([]));
        
        $result = $this->couponService->checkCouponRequirement($user, $coupon);
        $this->assertTrue($result);
        
        // 添加第二个场景，只测试总领取数量，不涉及注册时间
        $coupon2 = $this->createMock(Coupon::class);
        $requirement = new Requirement();
        $requirement->setType(RequirementType::TOTAL_GATHER_COUNT);
        $requirement->setValue(5); // 最多领取5张
        
        $requirements = new ArrayCollection([$requirement]);
        $coupon2->method('getRequirements')->willReturn($requirements);
        
        // 用户已经领取了3张，未超过限制
        $this->codeRepository->expects($this->once())
            ->method('count')
            ->with([
                'coupon' => $coupon2,
                'owner' => $user,
            ])
            ->willReturn(3);
        
        $result2 = $this->couponService->checkCouponRequirement($user, $coupon2);
        $this->assertTrue($result2);
    }
    
    public function testDetectCouponWithEvent(): void
    {
        $coupon = $this->createMock(Coupon::class);
        
        // 设置事件返回优惠券
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (DetectCouponEvent $event) use ($coupon) {
                $this->assertEquals('123', $event->getCouponId());
                $event->setCoupon($coupon);
                return $event;
            });
        
        // 如果从事件中获取了优惠券，不应该调用仓库
        $this->couponRepository->expects($this->never())
            ->method('findOneBy');
        
        $result = $this->couponService->detectCoupon('123');
        
        $this->assertSame($coupon, $result);
    }
    
    public function testDetectCouponBySn(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $couponId = 'COUPON123';
        
        // 事件不返回优惠券
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn(new DetectCouponEvent());
        
        // 通过SN查找
        $this->couponRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['sn' => $couponId])
            ->willReturn($coupon);
        
        $result = $this->couponService->detectCoupon($couponId);
        
        $this->assertSame($coupon, $result);
    }
    
    public function testDetectCouponById(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $couponId = '123';
        
        // 事件不返回优惠券
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn(new DetectCouponEvent());
        
        // 设置寻找行为，确保找到优惠券
        $this->couponRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria) use ($couponId, $coupon) {
                if ($criteria === ['sn' => $couponId]) {
                    return null;
                }
                
                if ($criteria === ['id' => $couponId]) {
                    return $coupon;
                }
                
                return null;
            });
        
        $result = $this->couponService->detectCoupon($couponId);
        
        $this->assertSame($coupon, $result);
    }
    
    public function testDetectCouponNotFound(): void
    {
        $couponId = '123';
        
        // 事件不返回优惠券
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturn(new DetectCouponEvent());
        
        // 设置寻找行为
        $this->couponRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['sn' => $couponId], null],
                [['id' => $couponId], null]
            ]);
        
        $this->expectException(CouponNotFoundException::class);
        $this->couponService->detectCoupon($couponId);
    }
    
    public function testLockCode(): void
    {
        $code = new Code();
        $code->setSn('CODE12345');
        
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CodeLockedEvent::class));
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $this->couponService->lockCode($code);
        
        // 由于 Code 实体没有 getLockTime/setLockTime 方法，我们不能测试它们
        // 只测试实体管理器已被调用
        $this->addToAssertionCount(1);
    }
    
    public function testUnlockCode(): void
    {
        $code = new Code();
        $code->setSn('CODE12345');
        // 不再设置 lockTime 和 lockId 属性
        
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CodeUnlockEvent::class));
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $this->couponService->unlockCode($code);
        
        // 由于 Code 实体没有 getUnlockTime/setUnlockTime 方法，我们不能测试它们
        // 只测试实体管理器已被调用
        $this->addToAssertionCount(1);
    }
    
    public function testRedeemCode(): void
    {
        $code = new Code();
        $code->setSn('CODE12345');
        
        $extra = new \stdClass();
        $extra->testData = 'test';
        
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CodeRedeemEvent::class));
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $this->couponService->redeemCode($code, $extra);
        
        $this->assertNotNull($code->getUseTime());
    }
    
    /**
     * 测试getQrcodeUrl方法
     */
    public function testGetQrcodeUrl(): void
    {
        $code = new Code();
        $code->setSn('CODE12345');
        
        $expectedUrl = 'https://example.com/qrcode/CODE12345';
        
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('qr_code_generate', ['data' => 'CODE12345'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn($expectedUrl);
        
        $result = $this->couponService->getQrcodeUrl($code);
        
        $this->assertEquals($expectedUrl, $result);
    }
}
