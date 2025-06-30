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

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '更新优惠券码使用渠道')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'UpdateCouponCodeUseChannel')]
#[Log]
class UpdateCouponCodeUseChannel extends LockableProcedure
{
    #[MethodParam(description: '券码')]
    public string $code;

    #[MethodParam(description: '使用渠道')]
    public string $useChannel;

    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly ChannelRepository $channelRepository,
    ) {
    }

    public function execute(): array
    {
        $code = $this->codeRepository->findOneBy([
            'owner' => $this->security->getUser(),
            'sn' => $this->code,
        ]);
        if ($code === null) {
            throw new ApiException('找不到券码');
        }

        $channel = null;
        if ($this->useChannel !== '') {
            $channel = $this->channelRepository->find($this->useChannel);
            if ($channel === null) {
                throw new ApiException('找不到渠道');
            }
        }

        $code->setUseChannel($channel);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        return [
            '__message' => '更新成功',
        ];
    }
}
