<?php

namespace Tourze\CouponCoreBundle\Procedure\Code;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CouponCoreBundle\Entity\ReadStatus;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineDirectInsertBundle\Service\DirectInsertService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag(name: '优惠券模块')]
#[MethodDoc(summary: '更新券码被查看情况')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodExpose(method: 'UpdateCodeReadStatus')]
#[Log]
#[WithMonologChannel(channel: 'procedure')]
class UpdateCodeReadStatus extends LockableProcedure
{
    public function __construct(
        private readonly CodeRepository $codeRepository,
        private readonly DirectInsertService $directInsertService,
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
            if ($code->getReadStatus() !== null) {
                continue;
            }

            try {
                $readStatus = new ReadStatus();
                $readStatus->setCode($code);
                $this->directInsertService->directInsert($readStatus);
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
