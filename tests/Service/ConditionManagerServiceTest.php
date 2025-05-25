<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Service\ConditionHandlerFactory;
use Tourze\CouponCoreBundle\Service\ConditionManagerService;

class ConditionManagerServiceTest extends TestCase
{
    private ConditionManagerService $conditionManager;
    private ConditionHandlerFactory $handlerFactory;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->handlerFactory = $this->createMock(ConditionHandlerFactory::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->conditionManager = new ConditionManagerService(
            $this->handlerFactory,
            $this->entityManager
        );
    }

    public function test_service_instantiation(): void
    {
        $this->assertInstanceOf(ConditionManagerService::class, $this->conditionManager);
    }

    public function test_check_requirements_with_empty_conditions(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $user = $this->createMock(UserInterface::class);
        
        $coupon->expects($this->once())
            ->method('getRequirementConditions')
            ->willReturn(new \Doctrine\Common\Collections\ArrayCollection([]));

        $result = $this->conditionManager->checkRequirements($coupon, $user);
        
        $this->assertTrue($result);
    }

    public function test_check_requirements_with_disabled_condition(): void
    {
        $coupon = $this->createMock(Coupon::class);
        $user = $this->createMock(UserInterface::class);
        $condition = $this->createMock(\Tourze\CouponCoreBundle\Interface\RequirementInterface::class);
        
        $condition->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
            
        $coupon->expects($this->once())
            ->method('getRequirementConditions')
            ->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$condition]));

        $result = $this->conditionManager->checkRequirements($coupon, $user);
        
        $this->assertTrue($result);
    }
} 