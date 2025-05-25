<?php

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 订单上下文
 */
class OrderContext
{
    public function __construct(
        private readonly string $totalAmount,
        private readonly array $items = [],
        private readonly array $categories = [],
        private readonly array $metadata = []
    ) {}

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function hasAnyCategory(array $categoryIds): bool
    {
        return !empty(array_intersect($this->categories, $categoryIds));
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
}
