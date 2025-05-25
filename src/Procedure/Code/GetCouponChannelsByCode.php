<?php

namespace Tourze\CouponCoreBundle\Procedure\Code;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Entity\Channel;
use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;

#[MethodTag('优惠券模块')]
#[MethodDoc('获取code channel信息')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('GetCouponChannelsByCode')]
class GetCouponChannelsByCode extends CacheableProcedure
{
    #[MethodParam('券码')]
    public string $code;

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $code = $this->codeRepository->findOneBy([
            'sn' => $this->code,
            'owner' => $this->security->getUser(),
        ]);
        if (!$code) {
            throw new ApiException('找不到券码');
        }
        $result = [
            'currentChannel' => $code->getChannel()?->retrievePlainArray(),
        ];
        $coupon = $code->getCoupon();
        $channels = $coupon->getChannels();
        foreach ($channels as $channel) {
            $result['channels'][] = $channel->retrievePlainArray();
        }

        return $result;
    }

    public function getCacheKey(JsonRpcRequest $request): string
    {
        $key = static::buildParamCacheKey($request->getParams());
        if ($this->security->getUser()) {
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
        yield CacheHelper::getClassTags(Code::class);
        yield CacheHelper::getClassTags(Channel::class);
    }
}
