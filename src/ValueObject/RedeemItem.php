<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 兑换商品项目
 *
 * @psalm-type RedeemArray = array{
 *     sku_id: string|int,
 *     quantity: int,
 *     unit_price?: string|float|int,
 *     name?: string|null
 * }
 */
class RedeemItem
{
    /**
     * @param numeric-string $unitPrice
     */
    public function __construct(
        private readonly string $skuId,
        private readonly int $quantity,
        private readonly string $unitPrice = '0.00',
        private readonly ?string $name = null,
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

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * 计算兑换总价
     *
     * @return numeric-string
     */
    public function getSubtotal(): string
    {
        return bcmul($this->unitPrice, sprintf('%.0f', $this->quantity), 2);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $unitPrice = $data['unit_price'] ?? '0.00';
        if (!is_string($unitPrice) || !is_numeric($unitPrice)) {
            $unitPrice = sprintf('%.2f', is_numeric($unitPrice) ? $unitPrice : 0.0);
        }
        /** @var numeric-string $unitPrice */

        $skuId = $data['sku_id'] ?? '';
        $quantity = $data['quantity'] ?? 0;

        return new self(
            is_scalar($skuId) ? (string) $skuId : '',
            is_scalar($quantity) ? max(0, (int) $quantity) : 0,
            $unitPrice,
            isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
        );
    }

    /**
     * @return RedeemArray
     */
    public function toArray(): array
    {
        return [
            'sku_id' => $this->skuId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'name' => $this->name,
        ];
    }
}
