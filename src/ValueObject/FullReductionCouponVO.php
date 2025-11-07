<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\AllocationRule;
use Tourze\CouponCoreBundle\Enum\CouponType;

/**
 * 满减券价值对象
 *
 * @psalm-type FullReductionArray = array{
 *     code: string,
 *     type?: string,
 *     name?: string|null,
 *     valid_from?: string|null,
 *     valid_to?: string|null,
 *     scope?: array<string, mixed>,
 *     condition?: array<string, mixed>,
 *     benefit?: array<string, mixed>,
 *     metadata?: array<string, mixed>
 * }
 */
class FullReductionCouponVO extends CouponVO
{
    /**
     * @param numeric-string|null $discountAmount
     */
    public function __construct(
        string $code,
        CouponType $type,
        ?string $name,
        ?\DateTimeImmutable $validFrom,
        ?\DateTimeImmutable $validTo,
        CouponScopeVO $scope,
        CouponConditionVO $condition,
        CouponBenefitVO $benefit,
        array $metadata = [],
        private readonly ?string $discountAmount = null,
        private readonly AllocationRule $allocationRule = AllocationRule::PROPORTIONAL,
    ) {
        parent::__construct(
            $code,
            $type,
            $name,
            $validFrom,
            $validTo,
            $scope,
            $condition,
            $benefit,
            $metadata
        );
    }

    /**
     * @return numeric-string|null
     */
    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount ?? $this->getBenefit()->getDiscountAmount();
    }

    public function getAllocationRule(): AllocationRule
    {
        return $this->allocationRule;
    }

    /**
     * @param FullReductionArray $data
     */
    public static function fromArray(array $data): self
    {
        $base = self::resolveBaseArguments($data);
        /** @var CouponBenefitVO $benefit */
        $benefit = $base['benefit'];

        return new self(
            $base['code'],
            $base['type'],
            $base['name'],
            $base['validFrom'],
            $base['validTo'],
            $base['scope'],
            $base['condition'],
            $benefit,
            $base['metadata'],
            $benefit->getDiscountAmount(),
            $benefit->getAllocationRule(),
        );
    }
}
