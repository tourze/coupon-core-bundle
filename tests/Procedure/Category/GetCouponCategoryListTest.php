<?php

namespace Tourze\CouponCoreBundle\Tests\Procedure\Category;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCoreBundle\Procedure\Category\GetCouponCategoryList;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;

class GetCouponCategoryListTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $procedure = new GetCouponCategoryList($categoryRepository);
        
        $this->assertInstanceOf(GetCouponCategoryList::class, $procedure);
    }
}