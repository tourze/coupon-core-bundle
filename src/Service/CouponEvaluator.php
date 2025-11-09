<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\CouponCoreBundle\Exception\CouponEvaluationException;
use Tourze\CouponCoreBundle\Service\Evaluator\CouponEvaluationStrategyInterface;
use Tourze\CouponCoreBundle\ValueObject\CouponApplicationResult;
use Tourze\CouponCoreBundle\ValueObject\CouponEvaluationContext;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;

#[WithMonologChannel(channel: 'coupon_core')]
readonly class CouponEvaluator
{
    /**
     * @param iterable<CouponEvaluationStrategyInterface> $strategies
     */
    public function __construct(
        #[TaggedIterator('coupon.evaluator.strategy')] private iterable $strategies,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @throws CouponEvaluationException
     */
    public function evaluate(CouponVO $coupon, CouponEvaluationContext $context): CouponApplicationResult
    {
        $this->assertValid($coupon, $context->getEvaluateTime(), $context->getUser());

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($coupon)) {
                return $strategy->evaluate($coupon, $context);
            }
        }

        throw new CouponEvaluationException(sprintf('暂不支持优惠券类型: %s', $coupon->getType()->value));
    }

    private function assertValid(CouponVO $coupon, \DateTimeInterface $now, ?UserInterface $user): void
    {
        if (!$coupon->isWithinValidity($now)) {
            $message = sprintf('优惠券 %s 已过期或未生效', $coupon->getCode());
            $this->logger?->warning($message, [
                'code' => $coupon->getCode(),
                'user' => $user?->getUserIdentifier(),
            ]);
            throw new CouponEvaluationException($message);
        }
    }
}
