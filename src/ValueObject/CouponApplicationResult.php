<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 优惠券应用结果
 *
 * @psalm-type AllocationDetail = array{sku_id: string, amount: string}
 * @psalm-type ResultMetadata = array<string, mixed>
 */
class CouponApplicationResult
{
    /**
     * @param list<AllocationDetail> $allocations
     * @param list<GiftItem> $giftItems
     * @param list<RedeemItem> $redeemItems
     * @param list<string> $messages
     * @param ResultMetadata $metadata
     */
    public function __construct(
        private readonly string $couponCode,
        string $discountAmount = '0.00',
        private readonly array $allocations = [],
        private readonly array $giftItems = [],
        private readonly array $redeemItems = [],
        private readonly bool $shouldMarkOrderPaid = false,
        private readonly array $messages = [],
        private readonly array $metadata = [],
    ) {
        $this->discountAmount = self::normalizeAmount($discountAmount);
    }

    /**
     * @var numeric-string
     */
    private string $discountAmount;

    public function getCouponCode(): string
    {
        return $this->couponCode;
    }

    /**
     * @return numeric-string
     */
    public function getDiscountAmount(): string
    {
        return $this->discountAmount;
    }

    /**
     * @return list<AllocationDetail>
     */
    public function getAllocations(): array
    {
        return $this->allocations;
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
        return $this->shouldMarkOrderPaid;
    }

    /**
     * @return list<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return ResultMetadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function hasDiscount(): bool
    {
        return bccomp($this->discountAmount, '0.00', 2) > 0;
    }

    public function hasGifts(): bool
    {
        return [] !== $this->giftItems;
    }

    public function hasRedeemItems(): bool
    {
        return [] !== $this->redeemItems;
    }

    public static function empty(string $couponCode): self
    {
        return new self($couponCode);
    }

    public function merge(self $other): self
    {
        return new self(
            $this->couponCode,
            bcadd($this->discountAmount, $other->discountAmount, 2),
            array_merge($this->allocations, $other->allocations),
            array_merge($this->giftItems, $other->giftItems),
            array_merge($this->redeemItems, $other->redeemItems),
            $this->shouldMarkOrderPaid || $other->shouldMarkOrderPaid,
            array_merge($this->messages, $other->messages),
            array_merge($this->metadata, $other->metadata),
        );
    }

    /**
     * @return numeric-string
     */
    private static function normalizeAmount(string|int|float $amount): string
    {
        if (is_string($amount) && is_numeric($amount)) {
            return sprintf('%.2f', (float) $amount);
        }

        return sprintf('%.2f', (float) $amount);
    }
}
