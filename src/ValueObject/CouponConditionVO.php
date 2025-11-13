<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 优惠券使用条件
 *
 * @psalm-type BuyRequirement = array{sku_id: string|int, quantity: int}
 * @psalm-type ConditionArray = array{
 *     threshold_amount?: string|float|int,
 *     min_quantity?: int,
 *     buy_requirements?: array<int, BuyRequirement>,
 *     max_gifts?: int,
 *     tiers?: array<int, array{
 *         threshold_amount: string|float|int,
 *         gifts: array<int, array{sku_id: string|int, gtin?: string|null, quantity: int, name?: string|null}>
 *     }>,
 *     max_redeem_quantity?: int,
 *     priority_skus?: array<int, string|int>,
 *     no_threshold?: bool,
 *     required_spu_ids?: array<int, string|int>
 * }
 */
class CouponConditionVO
{
    /**
     * @param numeric-string|null $thresholdAmount
     * @param list<array{sku_id: string|int, quantity: int}> $buyRequirements
     * @param list<FullGiftTier> $giftTiers
     * @param list<string> $prioritySkuIds
     * @param list<string> $requiredSpuIds
     */
    public function __construct(
        private readonly ?string $thresholdAmount = null,
        private readonly int $minQuantity = 0,
        private readonly array $buyRequirements = [],
        private readonly int $maxGifts = 0,
        private readonly array $giftTiers = [],
        private readonly int $maxRedeemQuantity = 0,
        private readonly array $prioritySkuIds = [],
        private readonly bool $noThreshold = false,
        private readonly array $requiredSpuIds = [],
    ) {
    }

    /**
     * @return numeric-string|null
     */
    public function getThresholdAmount(): ?string
    {
        return $this->thresholdAmount;
    }

    public function getMinQuantity(): int
    {
        return $this->minQuantity;
    }

    /**
     * @return list<array{sku_id: string|int, quantity: int}>
     */
    public function getBuyRequirements(): array
    {
        return $this->buyRequirements;
    }

    public function getMaxGifts(): int
    {
        return $this->maxGifts;
    }

    /**
     * @return list<FullGiftTier>
     */
    public function getGiftTiers(): array
    {
        return $this->giftTiers;
    }

    public function getMaxRedeemQuantity(): int
    {
        return $this->maxRedeemQuantity;
    }

    /**
     * @return list<string>
     */
    public function getPrioritySkuIds(): array
    {
        return $this->prioritySkuIds;
    }

    /**
     * @return list<string>
     */
    public function getRequiredSpuIds(): array
    {
        return $this->requiredSpuIds;
    }

    /**
     * 是否为无门槛优惠券
     */
    public function isNoThreshold(): bool
    {
        return $this->noThreshold;
    }

    /**
     * 根据订单金额匹配可用的满赠档位
     *
     * @param numeric-string $orderAmount
     */
    public function matchGiftTier(string $orderAmount): ?FullGiftTier
    {
        if ([] === $this->giftTiers) {
            return null;
        }

        $tiers = FullGiftTier::sortByThresholdDescending($this->giftTiers);
        foreach ($tiers as $tier) {
            if (bccomp($orderAmount, $tier->getThresholdAmount(), 2) >= 0) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $thresholdAmount = self::normalizeThresholdAmount($data['threshold_amount'] ?? null);
        $buyRequirements = self::parseBuyRequirements($data['buy_requirements'] ?? []);
        $tiers = FullGiftTier::sortByThresholdDescending(self::parseGiftTiers($data['tiers'] ?? []));
        $prioritySkuIds = self::normalizePrioritySkuIds($data['priority_skus'] ?? []);
        $requiredSpuIds = self::parseRequiredSpuIds($data['required_spu_ids'] ?? []);

        $minQuantity = $data['min_quantity'] ?? 0;
        $maxGifts = $data['max_gifts'] ?? 0;
        $maxRedeemQuantity = $data['max_redeem_quantity'] ?? 0;

        return new self(
            $thresholdAmount,
            is_scalar($minQuantity) ? max(0, (int) $minQuantity) : 0,
            $buyRequirements,
            is_scalar($maxGifts) ? max(0, (int) $maxGifts) : 0,
            $tiers,
            is_scalar($maxRedeemQuantity) ? max(0, (int) $maxRedeemQuantity) : 0,
            $prioritySkuIds,
            (bool) ($data['no_threshold'] ?? false),
            $requiredSpuIds
        );
    }

    /**
     * @return ConditionArray
     */
    public function toArray(): array
    {
        $result = [
            'min_quantity' => $this->minQuantity,
            'buy_requirements' => $this->buyRequirements,
            'max_gifts' => $this->maxGifts,
            'tiers' => array_map(
                static fn (FullGiftTier $tier): array => [
                    'threshold_amount' => $tier->getThresholdAmount(),
                    'gifts' => array_map(
                        static fn (GiftItem $gift): array => $gift->toArray(),
                        $tier->getGifts()
                    ),
                ],
                $this->giftTiers
            ),
            'max_redeem_quantity' => $this->maxRedeemQuantity,
            'priority_skus' => $this->prioritySkuIds,
            'no_threshold' => $this->noThreshold,
            'required_spu_ids' => $this->requiredSpuIds,
        ];

        if (null !== $this->thresholdAmount) {
            $result['threshold_amount'] = $this->thresholdAmount;
        }

        return $result;
    }

    /**
     * @param mixed $amount
     * @return numeric-string|null
     */
    private static function normalizeThresholdAmount(mixed $amount): ?string
    {
        if (null === $amount) {
            return null;
        }

        if (is_string($amount) && is_numeric($amount)) {
            return sprintf('%.2f', (float) $amount);
        }

        if (is_numeric($amount)) {
            return sprintf('%.2f', (float) $amount);
        }

        return '0.00';
    }

    /**
     * @param mixed $source
     * @return list<array{sku_id: int, quantity: int}>
     */
    private static function parseBuyRequirements(mixed $source): array
    {
        $requirements = [];
        foreach (is_array($source) ? $source : [] as $item) {
            if (!is_array($item) || !isset($item['sku_id'], $item['quantity'])) {
                continue;
            }
            $skuId = $item['sku_id'];
            $quantity = $item['quantity'];
            if (!is_scalar($skuId) || !is_scalar($quantity)) {
                continue;
            }
            $requirements[] = [
                'sku_id' => (int) $skuId,
                'quantity' => max(0, (int) $quantity),
            ];
        }

        return $requirements;
    }

    /**
     * @param mixed $source
     * @return list<FullGiftTier>
     */
    private static function parseGiftTiers(mixed $source): array
    {
        $tiers = [];
        foreach (is_array($source) ? $source : [] as $tier) {
            if (is_array($tier) && isset($tier['threshold_amount'])) {
                /** @var array{threshold_amount: string|float|int, gifts: array<int, array{sku_id: string|int, gtin?: string, quantity: int, name?: string|null}>} $tier */
                $tiers[] = FullGiftTier::fromArray($tier);
            }
        }

        return $tiers;
    }

    /**
     * @param mixed $source
     * @return list<string>
     */
    private static function normalizePrioritySkuIds(mixed $source): array
    {
        if (!is_array($source)) {
            return [];
        }

        $result = [];
        foreach ($source as $value) {
            if (is_int($value) || is_string($value)) {
                $result[] = (string) $value;
            }
        }

        return $result;
    }

    /**
     * @param mixed $source
     * @return list<string>
     */
    private static function parseRequiredSpuIds(mixed $source): array
    {
        if (!is_array($source)) {
            return [];
        }

        $result = [];
        foreach ($source as $value) {
            if (is_int($value) || is_string($value)) {
                $result[] = (string) $value;
            }
        }

        return $result;
    }
}
