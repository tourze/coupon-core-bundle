<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Exception\PickCodeNotFoundException;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodDoc(summary: '领取优惠券')]
#[MethodTag(name: '优惠券模块')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GatherCoupon')]
#[Log]
class GatherCoupon extends LockableProcedure
{
    #[MethodParam(description: '优惠券ID')]
    public string $couponId;

    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CouponService $codeService,
        private readonly NormalizerInterface $normalizer,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $coupon = $this->couponRepository->findOneBy([
            'id' => $this->couponId,
        ]);
        if (null === $coupon) {
            throw new ApiException('找不到指定优惠券');
        }

        $user = $this->security->getUser();
        if (null === $user) {
            throw new ApiException('用户未登录');
        }

        // TODO: 重新实现条件检查逻辑
        // try {
        //     $this->conditionManager->checkRequirements($coupon, $this->security->getUser());
        // } catch (CouponRequirementException $exception) {
        //     throw new ApiException($exception->getMessage());
        // }

        try {
            $code = $this->codeService->pickCode($user, $coupon);
        } catch (PickCodeNotFoundException $e) {
            throw new ApiException('优惠券已被抢光', $e->getCode(), previous: $e);
        }

        if (null === $code) {
            throw new ApiException('优惠券领取失败');
        }

        $code->setGatherTime(CarbonImmutable::now());
        $expireDay = $coupon->getExpireDay() ?? 30; // 默认30天过期
        $code->setExpireTime(CarbonImmutable::now()->addDays((int) $expireDay)); // 过期时间
        $code->setOwner($user);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $result = $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
        if (!is_array($result)) {
            throw new ApiException('序列化失败');
        }
        /** @var array<string, mixed> $result */
        $result['__message'] = '领取成功';

        return $result;
    }
}
