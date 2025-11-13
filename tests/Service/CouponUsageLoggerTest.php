<?php

namespace Tourze\CouponCoreBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Entity\CouponAllocationDetail;
use Tourze\CouponCoreBundle\Entity\CouponUsageLog;
use Tourze\CouponCoreBundle\Service\CouponUsageLogger;

/**
 * @internal
 */
#[CoversClass(CouponUsageLogger::class)]
final class CouponUsageLoggerTest extends TestCase
{
    public function testLogUsagePersistsEntities(): void
    {
        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects(self::exactly(1 + 1))
            ->method('persist')
            ->with(self::callback(static function (object $entity): bool {
                return $entity instanceof CouponUsageLog || $entity instanceof CouponAllocationDetail;
            }));

        $em->expects(self::once())->method('flush');

        $logger = new CouponUsageLogger($em);
        $logger->logUsage(
            couponCode: 'CODE',
            couponType: 'full_reduction',
            userIdentifier: 'user',
            orderId: 1,
            orderNumber: 'NO',
            discountAmount: '5.00',
            allocations: [
                ['sku_id' => 'SKU1', 'amount' => '5.00', 'order_product_id' => 2],
            ],
            metadata: []
        );
    }
}
