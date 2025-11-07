<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\CouponType;

/**
 * 兑换券价值对象
 *
 * @psalm-type RedeemCouponArray = array{
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
class RedeemCouponVO extends CouponVO
{
    /**
     * @param list<RedeemItem> $redeemItems
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
        private readonly array $redeemItems = [],
        private readonly int $maxRedeemQuantity = 0,
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
     * @return list<RedeemItem>
     */
    public function getRedeemItems(): array
    {
        $items = [] !== $this->redeemItems ? $this->redeemItems : $this->getBenefit()->getRedeemItems();

        if (0 === $this->maxRedeemQuantity) {
            return $items;
        }

        return array_map(
            fn (RedeemItem $item): RedeemItem => new RedeemItem(
                $item->getSkuId(),
                min($item->getQuantity(), $this->maxRedeemQuantity),
                $item->getUnitPrice(),
                $item->getName()
            ),
            $items
        );
    }

    public function getMaxRedeemQuantity(): int
    {
        if ($this->maxRedeemQuantity > 0) {
            return $this->maxRedeemQuantity;
        }

        return $this->getCondition()->getMaxRedeemQuantity();
    }

    /**
     * @param RedeemCouponArray $data
     */
    public static function fromArray(array $data): self
    {
        $base = self::resolveBaseArguments($data);
        /** @var CouponBenefitVO $benefit */
        $benefit = $base['benefit'];
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
            $benefit,
            $base['metadata'],
            $benefit->getRedeemItems(),
            $condition->getMaxRedeemQuantity()
        );
    }
}
