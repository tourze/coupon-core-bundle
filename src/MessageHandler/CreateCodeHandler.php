<?php

namespace Tourze\CouponCoreBundle\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tourze\CouponCoreBundle\Message\CreateCodeMessage;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\CouponCoreBundle\Service\CouponService;

#[AsMessageHandler]
class CreateCodeHandler
{
    public function __construct(
        private readonly CouponRepository $couponRepository,
        private readonly CouponService $codeService,
    ) {
    }

    public function __invoke(CreateCodeMessage $message): void
    {
        $coupon = $this->couponRepository->findOneBy([
            'id' => $message->getCouponId(),
            'valid' => true,
        ]);
        if ($coupon === null) {
            throw new \Exception('生成code时，找不到优惠券');
        }

        $c = $message->getQuantity();
        while ($c > 0) {
            $this->codeService->createOneCode($coupon);
            --$c;
        }
    }
}
