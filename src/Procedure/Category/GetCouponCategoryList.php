<?php

namespace Tourze\CouponCoreBundle\Procedure\Category;

use Carbon\CarbonImmutable;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '获取所有优惠券分类')]
#[MethodExpose(method: 'GetCouponCategoryList')]
class GetCouponCategoryList extends BaseProcedure
{
    #[MethodParam(description: '上级分类ID')]
    public int $parentId = 0;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function execute(): array
    {
        if ($this->parentId > 0) {
            $parent = $this->categoryRepository->find($this->parentId);
        } else {
            $parent = null;
        }

        $category = $this->categoryRepository->findBy([
            'parent' => $parent,
            'valid' => true,
        ]);

        $list = [];
        $now = CarbonImmutable::now();
        foreach ($category as $item) {
            if ($item->getEndTime() !== null) {
                if ($now->gt($item->getEndTime())) {
                    continue;
                }
            }
            if ($item->getStartTime() !== null) {
                if ($now->lt($item->getStartTime())) {
                    continue;
                }
            }

            $list[] = $item->retrieveReadArray();
        }

        return $list;
    }
}
