<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\CouponType;

/**
 * 买赠券价值对象
 *
 * @psalm-type BuyGiftArray = array{
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
class BuyGiftCouponVO extends CouponVO
{
    /**
     * @param list<array{sku_id: int, quantity: int}> $buyRequirements
     * @param list<GiftItem> $giftItems
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
        private readonly array $buyRequirements = [],
        private readonly array $giftItems = [],
        private readonly int $maxGifts = 0,
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
     * @return list<array{sku_id: int, quantity: int}>
     */
    public function getBuyRequirements(): array
    {
        return [] !== $this->buyRequirements ? $this->buyRequirements : $this->getCondition()->getBuyRequirements();
    }

    /**
     * @return list<GiftItem>
     */
    public function getGiftItems(): array
    {
        return [] !== $this->giftItems ? $this->giftItems : $this->getBenefit()->getGiftItems();
    }

    public function getMaxGifts(): int
    {
        if ($this->maxGifts > 0) {
            return $this->maxGifts;
        }

        return $this->getCondition()->getMaxGifts();
    }

    /**
     * @param BuyGiftArray $data
     */
    public static function fromArray(array $data): self
    {
        $base = self::resolveBaseArguments($data);
        /** @var CouponConditionVO $condition */
        $condition = $base['condition'];
        /** @var CouponBenefitVO $benefit */
        $benefit = $base['benefit'];

        return new self(
            $base['code'],
            $base['type'],
            $base['name'],
            $base['validFrom'],
            $base['validTo'],
            $base['scope'],
            $condition,
            $benefit,
            $base['metadata'],
            $condition->getBuyRequirements(),
            $benefit->getGiftItems(),
            $condition->getMaxGifts()
        );
    }
}
