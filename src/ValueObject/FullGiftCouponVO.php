<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\CouponType;

/**
 * 满赠券价值对象
 *
 * @psalm-type FullGiftArray = array{
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
class FullGiftCouponVO extends CouponVO
{
    /**
     * @param list<FullGiftTier> $giftTiers
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
        private readonly array $giftTiers = [],
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
     * @return list<FullGiftTier>
     */
    public function getGiftTiers(): array
    {
        return [] !== $this->giftTiers ? $this->giftTiers : $this->getCondition()->getGiftTiers();
    }

    /**
     * @param FullGiftArray $data
     */
    public static function fromArray(array $data): self
    {
        $base = self::resolveBaseArguments($data);
        /** @var CouponConditionVO $condition */
        $condition = $base['condition'];

        return new self(
            $base['code'],
            $base['type'],
            $base['name'],
            $base['validFrom'],
            $base['validTo'],
            $base['scope'],
            $condition,
            $base['benefit'],
            $base['metadata'],
            $condition->getGiftTiers()
        );
    }
}
