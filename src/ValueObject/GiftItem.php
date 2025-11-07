<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 赠品项目
 *
 * @psalm-type GiftArray = array{
 *     sku_id: int,
 *     gtin?: string|null,
 *     quantity: int,
 *     name?: string|null
 * }
 */
class GiftItem
{
    public function __construct(
        private readonly int $skuId,
        private readonly int $quantity,
        private readonly ?string $gtin = null,
        private readonly ?string $name = null,
    ) {
    }

    public function getSkuId(): int
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
     * @param GiftArray $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['sku_id']) ? (int) $data['sku_id'] : 0,
            isset($data['quantity']) ? max(0, (int) $data['quantity']) : 0,
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
