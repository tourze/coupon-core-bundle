<?php

namespace Tourze\CouponCoreBundle\Procedure\Code;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '激活指定优惠券码')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'ActiveCouponCode')]
#[Log]
class ActiveCouponCode extends LockableProcedure
{
    #[MethodParam(description: '券码')]
    public string $code;

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getMockResult(): ?array
    {
        return [
            '__message' => '激活成功',
            'expireTime' => CarbonImmutable::now()->format('Y-m-d H:i:s'),
        ];
    }

    public function execute(): array
    {
        $code = $this->codeRepository->findOneBy([
            'owner' => $this->security->getUser(),
            'sn' => $this->code,
            'needActive' => true,
        ]);
        if (null === $code) {
            throw new ApiException('找不到券码');
        }

        if (true !== (bool) $code->isNeedActive()) {
            throw new ApiException('该优惠券不需要激活');
        }

        if (true === (bool) $code->isActive()) {
            throw new ApiException('该优惠券已激活，不要重复操作');
        }

        $code->setActive(true);
        $code->setActiveTime(CarbonImmutable::now());
        // 激活后要重新计算时间的喔
        $coupon = $code->getCoupon();
        if (null !== $coupon && null !== $coupon->getActiveValidDay() && $coupon->getActiveValidDay() > 0) {
            $code->setExpireTime(CarbonImmutable::now()->addDays($coupon->getActiveValidDay()));
        }
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return [
            '__message' => '激活成功',
            'expireTime' => $code->getExpireTime()?->format('Y-m-d H:i:s'),
        ];
    }

    protected function getIdempotentCacheKey(JsonRpcRequest $request): ?string
    {
        $params = $request->getParams();
        if (null === $params) {
            return null;
        }
        $code = $params->get('code');

        return 'ActiveCouponCode-cache-' . (is_scalar($code) ? (string) $code : '');
    }
}
