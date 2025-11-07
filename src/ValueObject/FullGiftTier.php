<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 满赠档位配置
 *
 * @psalm-type TierArray = array{
 *     threshold_amount: string|float|int,
 *     gifts: array<int, array{sku_id: int, gtin?: string, quantity: int, name?: string|null}>
 * }
 */
class FullGiftTier
{
    /**
     * @param numeric-string $thresholdAmount
     * @param list<GiftItem> $gifts
     */
    public function __construct(
        private readonly string $thresholdAmount,
        private readonly array $gifts,
    ) {
    }

    /**
     * @return numeric-string
     */
    public function getThresholdAmount(): string
    {
        return $this->thresholdAmount;
    }

    /**
     * @return list<GiftItem>
     */
    public function getGifts(): array
    {
        return $this->gifts;
    }

    /**
     * @param TierArray $data
     */
    public static function fromArray(array $data): self
    {
        $threshold = self::normalizeAmount($data['threshold_amount'] ?? '0.00');

        $giftItems = [];
        foreach (is_array($data['gifts'] ?? null) ? $data['gifts'] : [] as $gift) {
            if (is_array($gift) && isset($gift['sku_id'], $gift['quantity'])) {
                $giftItems[] = GiftItem::fromArray($gift);
            }
        }

        return new self($threshold, $giftItems);
    }

    /**
     * @param list<FullGiftTier> $tiers
     * @return list<FullGiftTier>
     */
    public static function sortByThresholdDescending(array $tiers): array
    {
        usort($tiers, static function (self $a, self $b): int {
            return bccomp($b->thresholdAmount, $a->thresholdAmount, 2);
        });

        return $tiers;
    }

    /**
     * @return numeric-string
     */
    private static function normalizeAmount(string|int|float $amount): string
    {
        if (is_string($amount) && is_numeric($amount)) {
            return sprintf('%.2f', (float) $amount);
        }

        return sprintf('%.2f', is_numeric($amount) ? (float) $amount : 0.0);
    }
}
