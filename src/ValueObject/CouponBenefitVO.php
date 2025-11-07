<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\AllocationRule;

/**
 * 优惠券权益定义
 *
 * @psalm-type BenefitArray = array{
 *     discount_amount?: string|float|int,
 *     allocation?: string,
 *     gifts?: array<int, array{sku_id: int, gtin?: string|null, quantity: int, name?: string|null}>,
 *     redeem_items?: array<int, array{sku_id: string|int, quantity: int, unit_price?: string|float|int, name?: string|null}>,
 *     mark_paid?: bool,
 *     metadata?: array<string, mixed>
 * }
 */
class CouponBenefitVO
{
    /**
     * @param numeric-string|null $discountAmount
     * @param list<GiftItem> $giftItems
     * @param list<RedeemItem> $redeemItems
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private readonly ?string $discountAmount = null,
        private readonly AllocationRule $allocationRule = AllocationRule::PROPORTIONAL,
        private readonly array $giftItems = [],
        private readonly array $redeemItems = [],
        private readonly bool $markOrderPaid = false,
        private readonly array $metadata = [],
    ) {
    }

    /**
     * @return numeric-string|null
     */
    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount;
    }

    public function getAllocationRule(): AllocationRule
    {
        return $this->allocationRule;
    }

    /**
     * @return list<GiftItem>
     */
    public function getGiftItems(): array
    {
        return $this->giftItems;
    }

    /**
     * @return list<RedeemItem>
     */
    public function getRedeemItems(): array
    {
        return $this->redeemItems;
    }

    public function shouldMarkOrderPaid(): bool
    {
        return $this->markOrderPaid;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $discountAmount = self::normalizeAmountOrNull($data['discount_amount'] ?? null);
        $allocationValue = $data['allocation'] ?? AllocationRule::PROPORTIONAL->value;
        $allocation = AllocationRule::tryFrom(is_scalar($allocationValue) ? (string) $allocationValue : AllocationRule::PROPORTIONAL->value) ?? AllocationRule::PROPORTIONAL;
        $giftItems = self::parseGiftItems($data['gifts'] ?? []);
        $redeemItems = self::parseRedeemItems($data['redeem_items'] ?? []);
        $markOrderPaid = isset($data['mark_paid']) ? (bool) $data['mark_paid'] : false;
        $metadata = self::normalizeMetadata($data['metadata'] ?? []);

        return new self(
            $discountAmount,
            $allocation,
            $giftItems,
            $redeemItems,
            $markOrderPaid,
            $metadata
        );
    }

    /**
     * @return numeric-string|null
     */
    private static function normalizeAmountOrNull(mixed $amount): ?string
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
     * @return list<GiftItem>
     */
    private static function parseGiftItems(mixed $source): array
    {
        $items = [];
        foreach (is_array($source) ? $source : [] as $gift) {
            if (!is_array($gift) || !isset($gift['sku_id'], $gift['quantity'])) {
                continue;
            }
            /** @var array<string, mixed> $gift */
            $items[] = GiftItem::fromArray($gift);
        }

        return $items;
    }

    /**
     * @param mixed $source
     * @return list<RedeemItem>
     */
    private static function parseRedeemItems(mixed $source): array
    {
        $items = [];
        foreach (is_array($source) ? $source : [] as $item) {
            if (!is_array($item) || !isset($item['sku_id'], $item['quantity'])) {
                continue;
            }
            /** @var array<string, mixed> $item */
            $items[] = RedeemItem::fromArray($item);
        }

        return $items;
    }

    /**
     * @param mixed $metadata
     * @return array<string, mixed>
     */
    private static function normalizeMetadata(mixed $metadata): array
    {
        if (!is_array($metadata)) {
            return [];
        }

        // 确保所有键都是字符串
        $normalized = [];
        foreach ($metadata as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }

    /**
     * @return BenefitArray
     */
    public function toArray(): array
    {
        $result = [
            'allocation' => $this->allocationRule->value,
            'gifts' => array_map(static fn (GiftItem $gift): array => $gift->toArray(), $this->giftItems),
            'redeem_items' => array_map(static fn (RedeemItem $item): array => $item->toArray(), $this->redeemItems),
            'mark_paid' => $this->markOrderPaid,
            'metadata' => $this->metadata,
        ];

        if (null !== $this->discountAmount) {
            $result['discount_amount'] = $this->discountAmount;
        }

        return $result;
    }
}
