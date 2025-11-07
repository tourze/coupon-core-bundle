<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 优惠券计算时使用的订单项目
 *
 * @psalm-type OrderItemArray = array{
 *     sku_id: string|int,
 *     spu_id?: string|int|null,
 *     category_id?: string|int|null,
 *     gtin?: string|null,
 *     spu_gtin?: string|null,
 *     quantity: int,
 *     unit_price: string|float|int,
 *     selected?: bool
 * }
 */
class CouponOrderItem
{
    /**
     * @param numeric-string $unitPrice
     * @param numeric-string $subtotal
     */
    public function __construct(
        private readonly string $skuId,
        private readonly int $quantity,
        private readonly string $unitPrice,
        private readonly bool $selected = true,
        private readonly ?string $spuId = null,
        private readonly ?string $categoryId = null,
        private readonly ?string $gtin = null,
        private readonly ?string $spuGtin = null,
        private readonly string $subtotal = '0.00',
    ) {
    }

    public function getSkuId(): string
    {
        return $this->skuId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return numeric-string
     */
    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function isSelected(): bool
    {
        return $this->selected;
    }

    public function getSpuId(): ?string
    {
        return $this->spuId;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function getSpuGtin(): ?string
    {
        return $this->spuGtin;
    }

    /**
     * @return numeric-string
     */
    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    /**
     * 为项目分摊优惠
     *
     * @param numeric-string $discount
     */
    public function withAllocatedDiscount(string $discount): self
    {
        $discountedSubtotal = bcsub($this->subtotal, $discount, 2);
        if (bccomp($discountedSubtotal, '0.00', 2) < 0) {
            $discountedSubtotal = '0.00';
        }

        /** @var numeric-string $discountedSubtotal */
        $discountedSubtotal = $discountedSubtotal;
        $quantityString = sprintf('%.0f', $this->quantity);
        /** @var lowercase-string&numeric-string $quantityString */
        $quantityString = $quantityString;

        $effectiveUnitPrice = $this->quantity > 0
            ? bcdiv($discountedSubtotal, $quantityString, 2)
            : '0.00';

        return new self(
            $this->skuId,
            $this->quantity,
            $effectiveUnitPrice,
            $this->selected,
            $this->spuId,
            $this->categoryId,
            $this->gtin,
            $this->spuGtin,
            $discountedSubtotal
        );
    }

    /**
     * @param OrderItemArray $data
     */
    public static function fromArray(array $data): self
    {
        $skuId = (string) ($data['sku_id'] ?? '');
        $quantity = isset($data['quantity']) ? max(0, (int) $data['quantity']) : 0;

        $unitPrice = $data['unit_price'] ?? '0.00';
        if (!is_string($unitPrice)) {
            $unitPrice = sprintf('%.2f', is_numeric($unitPrice) ? $unitPrice : 0.0);
        }
        /** @var numeric-string $unitPrice */
        $unitPrice = $unitPrice;

        $multiplier = sprintf('%.0f', $quantity);
        /** @var lowercase-string&numeric-string $multiplier */
        $multiplier = $multiplier;

        $subtotal = bcmul($unitPrice, $multiplier, 2);
        /** @var numeric-string $subtotal */
        $subtotal = $subtotal;

        return new self(
            $skuId,
            $quantity,
            $unitPrice,
            isset($data['selected']) ? (bool) $data['selected'] : true,
            isset($data['spu_id']) ? (string) $data['spu_id'] : null,
            isset($data['category_id']) ? (string) $data['category_id'] : null,
            isset($data['gtin']) ? (string) $data['gtin'] : null,
            isset($data['spu_gtin']) ? (string) $data['spu_gtin'] : null,
            $subtotal
        );
    }

    /**
     * @return OrderItemArray
     */
    public function toArray(): array
    {
        return [
            'sku_id' => $this->skuId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'subtotal' => $this->subtotal,
            'selected' => $this->selected,
            'spu_id' => $this->spuId,
            'category_id' => $this->categoryId,
            'gtin' => $this->gtin,
            'spu_gtin' => $this->spuGtin,
        ];
    }
}
