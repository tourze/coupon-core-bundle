<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Coupon;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\CouponCoreBundle\Procedure\Coupon\GetCouponListByCategory;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class GetCouponListByCategoryTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $couponRepository = $this->createMock(CouponRepository::class);
        $security = $this->createMock(Security::class);
        
        $procedure = new GetCouponListByCategory($categoryRepository, $couponRepository, $security);
        
        $this->assertInstanceOf(GetCouponListByCategory::class, $procedure);
    }
}