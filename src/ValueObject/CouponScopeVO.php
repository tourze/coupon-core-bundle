<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\CouponScopeType;

/**
 * 优惠券适用范围
 *
 * @psalm-type ScopeArray = array{
 *     type?: string,
 *     include_skus?: array<int, string|int>,
 *     exclude_skus?: array<int, string|int>,
 *     include_spus?: array<int, string|int>,
 *     include_categories?: array<int, string|int>,
 *     include_gtins?: array<int, string>,
 *     exclude_gtins?: array<int, string>,
 *     include_spu_gtins?: array<int, string>,
 *     exclude_spu_gtins?: array<int, string>
 * }
 */
class CouponScopeVO
{
    /**
     * @param list<string> $includedSkuIds
     * @param list<string> $excludedSkuIds
     * @param list<string> $includedSpuIds
     * @param list<string> $includedCategoryIds
     * @param list<string> $includedGtins
     * @param list<string> $excludedGtins
     * @param list<string> $includedSpuGtins
     * @param list<string> $excludedSpuGtins
     */
    public function __construct(
        private readonly CouponScopeType $type,
        private readonly array $includedSkuIds = [],
        private readonly array $excludedSkuIds = [],
        private readonly array $includedSpuIds = [],
        private readonly array $includedCategoryIds = [],
        private readonly array $includedGtins = [],
        private readonly array $excludedGtins = [],
        private readonly array $includedSpuGtins = [],
        private readonly array $excludedSpuGtins = [],
    ) {
    }

    public function getType(): CouponScopeType
    {
        return $this->type;
    }

    /**
     * @return list<string>
     */
    public function getIncludedSkuIds(): array
    {
        return $this->includedSkuIds;
    }

    /**
     * @return list<string>
     */
    public function getExcludedSkuIds(): array
    {
        return $this->excludedSkuIds;
    }

    /**
     * @return list<string>
     */
    public function getIncludedSpuIds(): array
    {
        return $this->includedSpuIds;
    }

    /**
     * @return list<string>
     */
    public function getIncludedCategoryIds(): array
    {
        return $this->includedCategoryIds;
    }

    /**
     * @return list<string>
     */
    public function getIncludedGtins(): array
    {
        return $this->includedGtins;
    }

    /**
     * @return list<string>
     */
    public function getExcludedGtins(): array
    {
        return $this->excludedGtins;
    }

    /**
     * @return list<string>
     */
    public function getIncludedSpuGtins(): array
    {
        return $this->includedSpuGtins;
    }

    /**
     * @return list<string>
     */
    public function getExcludedSpuGtins(): array
    {
        return $this->excludedSpuGtins;
    }

    public function isSkuEligible(string $skuId, ?string $skuGtin = null, ?string $spuGtin = null): bool
    {
        if ($this->isSkuExcluded($skuId, $skuGtin, $spuGtin)) {
            return false;
        }

        if ($this->type === CouponScopeType::ALL) {
            return true;
        }

        if ($this->type === CouponScopeType::SKU) {
            return $this->isSkuIncluded($skuId, $skuGtin, $spuGtin);
        }

        return true;
    }

    private function isSkuExcluded(string $skuId, ?string $skuGtin, ?string $spuGtin): bool
    {
        if (in_array($skuId, $this->excludedSkuIds, true)) {
            return true;
        }
        if (null !== $skuGtin && in_array($skuGtin, $this->excludedGtins, true)) {
            return true;
        }
        return null !== $spuGtin && in_array($spuGtin, $this->excludedSpuGtins, true);
    }

    private function isSkuIncluded(string $skuId, ?string $skuGtin, ?string $spuGtin): bool
    {
        // 优先级：SKU GTIN > SPU GTIN > SKU ID
        if ([] !== $this->includedGtins) {
            return null !== $skuGtin && in_array($skuGtin, $this->includedGtins, true);
        }
        if ([] !== $this->includedSpuGtins) {
            return null !== $spuGtin && in_array($spuGtin, $this->includedSpuGtins, true);
        }
        return [] === $this->includedSkuIds || in_array($skuId, $this->includedSkuIds, true);
    }

    public function isSpuEligible(?string $spuId, ?string $spuGtin = null): bool
    {
        if (null === $spuId || CouponScopeType::SPU !== $this->type) {
            return true;
        }

        // 排除逻辑
        if (null !== $spuGtin && in_array($spuGtin, $this->excludedSpuGtins, true)) {
            return false;
        }

        // 包含逻辑：优先级 SPU GTIN > SPU ID
        if ([] !== $this->includedSpuGtins) {
            return null !== $spuGtin && in_array($spuGtin, $this->includedSpuGtins, true);
        }
        
        return [] === $this->includedSpuIds || in_array($spuId, $this->includedSpuIds, true);
    }

    public function isCategoryEligible(?string $categoryId): bool
    {
        if (null === $categoryId || CouponScopeType::CATEGORY !== $this->type) {
            return true;
        }

        return [] === $this->includedCategoryIds || in_array($categoryId, $this->includedCategoryIds, true);
    }

    /**
     * @param ScopeArray $data
     */
    public static function fromArray(array $data): self
    {
        $type = CouponScopeType::tryFrom((string) ($data['type'] ?? CouponScopeType::ALL->value)) ?? CouponScopeType::ALL;

        $includedSkuIds = array_values(array_map(
            static fn (string|int $value): string => (string) $value,
            is_array($data['include_skus'] ?? null) ? $data['include_skus'] : []
        ));

        $excludedSkuIds = array_values(array_map(
            static fn (string|int $value): string => (string) $value,
            is_array($data['exclude_skus'] ?? null) ? $data['exclude_skus'] : []
        ));

        $includedSpuIds = array_values(array_map(
            static fn (string|int $value): string => (string) $value,
            is_array($data['include_spus'] ?? null) ? $data['include_spus'] : []
        ));

        $includedCategoryIds = array_values(array_map(
            static fn (string|int $value): string => (string) $value,
            is_array($data['include_categories'] ?? null) ? $data['include_categories'] : []
        ));

        $includedGtins = array_values(array_map(
            static fn (string $value): string => $value,
            is_array($data['include_gtins'] ?? null) ? $data['include_gtins'] : []
        ));

        $excludedGtins = array_values(array_map(
            static fn (string $value): string => $value,
            is_array($data['exclude_gtins'] ?? null) ? $data['exclude_gtins'] : []
        ));

        $includedSpuGtins = array_values(array_map(
            static fn (string $value): string => $value,
            is_array($data['include_spu_gtins'] ?? null) ? $data['include_spu_gtins'] : []
        ));

        $excludedSpuGtins = array_values(array_map(
            static fn (string $value): string => $value,
            is_array($data['exclude_spu_gtins'] ?? null) ? $data['exclude_spu_gtins'] : []
        ));

        return new self(
            $type,
            $includedSkuIds,
            $excludedSkuIds,
            $includedSpuIds,
            $includedCategoryIds,
            $includedGtins,
            $excludedGtins,
            $includedSpuGtins,
            $excludedSpuGtins
        );
    }

    /**
     * @return ScopeArray
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'include_skus' => $this->includedSkuIds,
            'exclude_skus' => $this->excludedSkuIds,
            'include_spus' => $this->includedSpuIds,
            'include_categories' => $this->includedCategoryIds,
            'include_gtins' => $this->includedGtins,
            'exclude_gtins' => $this->excludedGtins,
            'include_spu_gtins' => $this->includedSpuGtins,
            'exclude_spu_gtins' => $this->excludedSpuGtins,
        ];
    }
}
