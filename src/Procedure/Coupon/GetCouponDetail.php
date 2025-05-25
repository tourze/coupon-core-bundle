<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodTag('优惠券模块')]
#[MethodDoc('读取优惠券详情')]
#[MethodExpose('GetCouponDetail')]
class GetCouponDetail extends BaseProcedure
{
    #[MethodParam('优惠券ID')]
    public string $couponId;

    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CodeRepository $codeRepository,
    ) {
    }

    public function execute(): array
    {
        $coupon = $this->couponRepository->findOneBy([
            'id' => $this->couponId,
            'valid' => true,
        ]);
        if (!$coupon) {
            throw new ApiException('找不到优惠券');
        }

        $result = $coupon->retrieveApiArray();

        $result['validCodeCount'] = $this->codeRepository->count([
            'coupon' => $coupon,
            'owner' => null,
            'useTime' => null,
            'valid' => true,
        ]);

        return $result;
    }
}
