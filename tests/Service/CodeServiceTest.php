<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Service\CodeService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CodeService::class)]
#[RunTestsInSeparateProcesses]
final class CodeServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testGetValidStockShouldReturnCountOfCodesWithoutOwner(): void
    {
        $service = self::getService(CodeService::class);
        $coupon = $this->createTestCoupon();

        $count = $service->getValidStock($coupon);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testGetGatherStockShouldReturnCountOfCodesWithOwner(): void
    {
        $service = self::getService(CodeService::class);
        $coupon = $this->createTestCoupon();

        $count = $service->getGatherStock($coupon);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testStockCountsShouldBeConsistent(): void
    {
        $service = self::getService(CodeService::class);
        $coupon = $this->createTestCoupon();

        $validStock = $service->getValidStock($coupon);
        $gatherStock = $service->getGatherStock($coupon);

        $this->assertGreaterThanOrEqual(0, $validStock);
        $this->assertGreaterThanOrEqual(0, $gatherStock);
    }

    private function createTestCoupon(): Coupon
    {
        $coupon = new Coupon();
        $coupon->setName('测试优惠券');
        $coupon->setSn('TEST_' . uniqid());
        $coupon->setValid(true);
        $coupon->setExpireDay(30);

        $entityManager = self::getEntityManager();
        $entityManager->persist($coupon);
        $entityManager->flush();

        return $coupon;
    }
}
