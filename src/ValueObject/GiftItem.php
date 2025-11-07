<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 赠品项目
 *
 * @psalm-type GiftArray = array{
 *     sku_id: string|int,
 *     gtin?: string|null,
 *     quantity: int,
 *     name?: string|null
 * }
 */
class GiftItem
{
    public function __construct(
        private readonly string $skuId,
        private readonly int $quantity,
        private readonly ?string $gtin = null,
        private readonly ?string $name = null,
    ) {
    }

    public function getSkuId(): string
    {
        return $this->skuId;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $skuId = $data['sku_id'] ?? '';
        $quantity = $data['quantity'] ?? 0;

        return new self(
            is_scalar($skuId) ? (string) $skuId : '',
            is_scalar($quantity) ? max(0, (int) $quantity) : 0,
            isset($data['gtin']) && is_string($data['gtin']) ? $data['gtin'] : null,
            isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
        );
    }

    /**
     * @return GiftArray
     */
    public function toArray(): array
    {
        return [
            'sku_id' => $this->skuId,
            'gtin' => $this->gtin,
            'quantity' => $this->quantity,
            'name' => $this->name,
        ];
    }
}
