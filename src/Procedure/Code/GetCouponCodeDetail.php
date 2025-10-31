<?php

namespace Tourze\CouponCoreBundle\Procedure\Code;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '获取优惠券详情信息')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetCouponCodeDetail')]
class GetCouponCodeDetail extends CacheableProcedure
{
    #[MethodParam(description: '优惠券ID')]
    public string $codeId;

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $code = $this->codeRepository->findOneBy([
            'id' => $this->codeId,
            'owner' => $this->security->getUser(),
        ]);
        if (null === $code) {
            throw new ApiException('找不到券码');
        }

        $normalized = $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
        if (!is_array($normalized)) {
            throw new ApiException('序列化失败');
        }

        /** @var array<string, mixed> $normalized */
        return $normalized;
    }

    public function getCacheKey(JsonRpcRequest $request): string
    {
        $params = $request->getParams();
        if (null === $params) {
            $key = 'no-params';
        } else {
            $key = static::buildParamCacheKey($params);
        }

        if (null !== $this->security->getUser()) {
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
        $params = $request->getParams();
        if (null === $params) {
            return;
        }

        $codeId = $params->get('codeId');
        $codeIdString = is_scalar($codeId) ? (string) $codeId : null;
        yield CacheHelper::getClassTags(Code::class, $codeIdString);
        yield CacheHelper::getClassTags(Coupon::class, $codeIdString);
    }
}
