<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service;

use Tourze\CouponCoreBundle\Entity\Code;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\ValueObject\BuyGiftCouponVO;
use Tourze\CouponCoreBundle\ValueObject\CouponVO;
use Tourze\CouponCoreBundle\ValueObject\FullGiftCouponVO;
use Tourze\CouponCoreBundle\ValueObject\FullReductionCouponVO;
use Tourze\CouponCoreBundle\ValueObject\RedeemCouponVO;

/**
 * 优惠券价值对象工厂
 */
class CouponVOFactory
{
    public function createFromCoupon(Coupon $coupon): CouponVO
    {
        return $this->createFromArray($this->buildBaseData($coupon));
    }

    public function createFromCouponCode(Code $code): CouponVO
    {
        $coupon = $code->getCoupon();
        if (null === $coupon) {
            throw new \InvalidArgumentException('优惠券码未关联优惠券');
        }

        $data = $this->buildBaseData($coupon);
        $data['code'] = (string) $code->getSn();
        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $metadata = array_merge($metadata, [
            'code_id' => $code->getId(),
            'code_expire_time' => $code->getExpireTime()?->format(\DateTimeInterface::ATOM),
            'code_gather_time' => $code->getGatherTime()?->format(\DateTimeInterface::ATOM),
        ]);
        $data['metadata'] = $metadata;

        return $this->createFromArray($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromArray(array $data): CouponVO
    {
        $type = CouponType::tryFrom((string) ($data['type'] ?? CouponType::FULL_REDUCTION->value)) ?? CouponType::FULL_REDUCTION;

        /** @var array{code: string, type?: string, name?: string|null, valid_from?: string|null, valid_to?: string|null, scope?: array<string, mixed>, condition?: array<string, mixed>, benefit?: array<string, mixed>, metadata?: array<string, mixed>} $data */
        return match ($type) {
            CouponType::FULL_REDUCTION => FullReductionCouponVO::fromArray($data),
            CouponType::FULL_GIFT => FullGiftCouponVO::fromArray($data),
            CouponType::REDEEM => RedeemCouponVO::fromArray($data),
            CouponType::BUY_GIFT => BuyGiftCouponVO::fromArray($data),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBaseData(Coupon $coupon): array
    {
        $configuration = $coupon->getConfiguration();

        $scope = is_array($configuration['scope'] ?? null) ? $configuration['scope'] : [];
        $condition = is_array($configuration['condition'] ?? null) ? $configuration['condition'] : [];
        $benefit = is_array($configuration['benefit'] ?? null) ? $configuration['benefit'] : [];
        $metadata = is_array($configuration['metadata'] ?? null) ? $configuration['metadata'] : [];

        $metadata = array_merge($metadata, [
            'coupon_id' => $coupon->getId(),
            'coupon_sn' => $coupon->getSn(),
            'expire_day' => $coupon->getExpireDay(),
            'coupon_type' => $coupon->getType()->value,
        ]);

        $validFrom = $coupon->getStartTime() ?? $coupon->getStartDateTime();
        $validTo = $coupon->getEndTime() ?? $coupon->getEndDateTime();

        return [
            'code' => $coupon->getSn() ?? '',
            'type' => $coupon->getType()->value,
            'name' => $coupon->getName(),
            'valid_from' => $validFrom?->format(\DateTimeInterface::ATOM),
            'valid_to' => $validTo?->format(\DateTimeInterface::ATOM),
            'scope' => $scope,
            'condition' => $condition,
            'benefit' => $benefit,
            'metadata' => $metadata,
        ];
    }
}
