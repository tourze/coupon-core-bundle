<?php

namespace Tourze\CouponCoreBundle\Procedure\Code;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Repository\ChannelRepository;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag('优惠券模块')]
#[MethodDoc('提交优惠券码使用渠道')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('SubmitCouponCodeChannel')]
#[Log]
class SubmitCouponCodeChannel extends LockableProcedure
{
    #[MethodParam('券码')]
    public string $code;

    #[MethodParam('使用渠道Id')]
    public string $channelId;

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly ChannelRepository $channelRepository,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $code = $this->codeRepository->findOneBy([
            'owner' => $this->security->getUser(),
            'sn' => $this->code,
        ]);
        if (!$code) {
            throw new ApiException('找不到券码');
        }

        $useChannel = $this->channelRepository->findOneBy(['id' => $this->channelId]);
        if (!$useChannel) {
            throw new ApiException('暂无该试用渠道~');
        }
        $code->setChannel($useChannel);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return [
            'currentChannel' => $useChannel->retrievePlainArray(),
        ];
    }
}
