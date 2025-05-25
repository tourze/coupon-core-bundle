<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use Carbon\Carbon;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Exception\CouponRequirementException;
use Tourze\CouponCoreBundle\Exception\PickCodeNotFoundException;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodDoc('领取优惠券')]
#[MethodTag('优惠券模块')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('GatherCoupon')]
#[Log]
class GatherCoupon extends LockableProcedure
{
    #[MethodParam('优惠券ID')]
    public string $couponId;

    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CodeRepository $codeRepository,
        private readonly CouponService $codeService,
        private readonly NormalizerInterface $normalizer,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $coupon = $this->couponRepository->findOneBy([
            'id' => $this->couponId,
        ]);
        if (!$coupon) {
            throw new ApiException('找不到指定优惠券');
        }

        $user = $this->security->getUser();

        // 查找是否满足领取条件
        try {
            $this->codeService->checkCouponRequirement($this->security->getUser(), $coupon);
        } catch (CouponRequirementException $exception) {
            throw new ApiException($exception->getMessage());
        }

        try {
            $code = $this->codeService->pickCode($user, $coupon);
        } catch (PickCodeNotFoundException $e) {
            throw new ApiException('优惠券已被抢光', $e->getCode(), previous: $e);
        }

        $code->setGatherTime(Carbon::now());
        $code->setExpireTime(Carbon::now()->addDays($coupon->getExpireDay())); // 过期时间
        $code->setOwner($user);
        $this->codeRepository->add($code);

        $result = $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
        $result['__message'] = '领取成功';

        return $result;
    }
}
