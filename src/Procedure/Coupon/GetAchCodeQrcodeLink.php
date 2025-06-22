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

#[MethodTag('优惠券模块')]
#[MethodDoc('获取优惠券二维码信息')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('GetAchCodeQrcodeLink')]
class GetAchCodeQrcodeLink extends LockableProcedure
{
    #[MethodParam('数据库优惠券ID')]
    public int $codeId;

    #[MethodParam('特殊参数，直接携带的skuId')]
    public string $skuId = '';

    #[MethodParam('特殊参数，直接携带的sku名称')]
    public string $skuName = '';

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly NormalizerInterface $normalizer,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $code = $this->codeRepository->findOneBy([
            'id' => $this->codeId,
            'owner' => $this->security->getUser(),
        ]);
        if ($code === null) {
            throw new ApiException('找不到优惠券码');
        }

        $codeData = [
            'couponCode' => $code->getSn(),
            'couponId' => $code->getCoupon()->getSn(),
            'identityId' => $this->security->getUser()->getUserIdentifier(),
            'genTime' => CarbonImmutable::now()->getTimestamp(),
        ];
        if ($this->skuId !== '') {
            $codeData['skuId'] = $this->skuId;
            $codeData['skuName'] = $this->skuName;
        }

        $codeData = Json::encode($codeData);

        $result = $this->normalizer->normalize($code, 'array', ['groups' => 'restful_read']);
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
