<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use Carbon\CarbonImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Entity\Category;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '通过分类获取优惠券')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetCouponListByCategory')]
class GetCouponListByCategory extends CacheableProcedure
{
    #[MethodParam(description: '上级分类ID')]
    public int $categoryId;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CouponRepository $couponRepository,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $category = $this->categoryRepository->find($this->categoryId);
        if ($category === null) {
            throw new ApiException('分类不存在');
        }

        $coupon = $this->couponRepository->findBy([
            'category' => $category,
        ]);

        $list = [];
        $now = CarbonImmutable::now();
        foreach ($coupon as $item) {
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

            $list[] = $item->retrieveApiArray();
        }

        return $list;
    }

    public function getCacheKey(JsonRpcRequest $request): string
    {
        $key = static::buildParamCacheKey($request->getParams());
        if ($this->security->getUser() !== null) {
            $key .= '-' . $this->security->getUser()->getUserIdentifier();
        }

        return $key;
    }

    public function getCacheDuration(JsonRpcRequest $request): int
    {
        return 60;
    }

    public function getCacheTags(JsonRpcRequest $request): iterable
    {
        yield CacheHelper::getClassTags(Category::class);
    }
}
