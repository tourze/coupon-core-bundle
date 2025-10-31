<?php

namespace Tourze\CouponCoreBundle\Procedure\Coupon;

use Carbon\CarbonImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Yiisoft\Json\Json;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '获取优惠券二维码信息')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'GetAchCodeQrcodeLink')]
class GetAchCodeQrcodeLink extends LockableProcedure
{
    #[MethodParam(description: '数据库优惠券ID')]
    public int $codeId;

    #[MethodParam(description: '特殊参数，直接携带的skuId')]
    public string $skuId = '';

    #[MethodParam(description: '特殊参数，直接携带的sku名称')]
    public string $skuName = '';

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
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
            throw new ApiException('找不到优惠券码');
        }

        $coupon = $code->getCoupon();
        if (null === $coupon) {
            throw new ApiException('优惠券信息不存在');
        }

        $user = $this->security->getUser();
        if (null === $user) {
            throw new ApiException('用户未登录');
        }

        $codeData = [
            'couponCode' => $code->getSn(),
            'couponId' => $coupon->getSn(),
            'identityId' => $user->getUserIdentifier(),
            'genTime' => CarbonImmutable::now()->getTimestamp(),
        ];
        if ('' !== $this->skuId) {
            $codeData['skuId'] = $this->skuId;
            $codeData['skuName'] = $this->skuName;
        }

        $codeData = Json::encode($codeData);

        $normalized = $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
        if (!is_array($normalized)) {
            throw new ApiException('序列化失败');
        }
        /** @var array<string, mixed> $normalized */
        $result = $normalized;
        $result['data'] = $codeData;
        $result['qrcodeUrl'] = $this->urlGenerator->generate('qr_code_generate', [
            'data' => $codeData,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $result;
    }

    public static function getMockResult(): ?array
    {
        return [
            'sn' => uniqid(),
            'data' => Json::encode([
                'skuId' => '123',
                'skuName' => '测试',
            ]),
            'qrcodeUrl' => 'https://pay.weixin.qq.com/wiki/doc/apiv3/assets/img/Practices/3_1_1_5.png',
        ];
    }
}
