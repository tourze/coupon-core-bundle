<?php

namespace Tourze\CouponCoreBundle\Procedure\Code;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Entity\ReadStatus;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineAsyncBundle\Service\DoctrineService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag('优惠券模块')]
#[MethodDoc('更新券码被查看情况')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('UpdateCodeReadStatus')]
#[Log]
#[WithMonologChannel('procedure')]
class UpdateCodeReadStatus extends LockableProcedure
{
    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly DoctrineService $doctrineService,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(): array
    {
        $codes = $this->codeRepository->findBy([
            'owner' => $this->security->getUser(),
        ]);

        foreach ($codes as $code) {
            if ($code->getReadStatus()) {
                continue;
            }

            try {
                $readStatus = new ReadStatus();
                $readStatus->setCode($code);
                $this->doctrineService->directInsert($readStatus);
            } catch (UniqueConstraintViolationException $exception) {
                $this->logger->warning('更新券码被查看情况时发现重复数据', [
                    'code' => $code,
                    'exception' => $exception,
                ]);
            }
        }

        return [
            '__message' => '更新成功',
        ];
    }
}
