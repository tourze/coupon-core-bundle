<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator\Helper;

use Tourze\CouponCoreBundle\ValueObject\CouponOrderItem;
use Tourze\CouponCoreBundle\ValueObject\GiftItem;

/**
 * @internal
 */
class GiftCalculator
{
    /**
     * @param list<GiftItem> $giftItems
     * @return list<GiftItem>
     */
    public function normalizeGiftQuantities(array $giftItems, int $maxGifts): array
    {
        $result = [];
        $totalGiven = 0;
        foreach ($giftItems as $gift) {
            $quantity = $gift->getQuantity();
            if ($quantity <= 0) {
                continue;
            }

            if ($maxGifts > 0) {
                $remaining = $maxGifts - $totalGiven;
                if ($remaining <= 0) {
                    break;
                }
                $quantity = min($quantity, $remaining);
            }

            $result[] = new GiftItem($gift->getSkuId(), $quantity, $gift->getGtin(), $gift->getName());
            $totalGiven += $quantity;
        }

        return $result;
    }

    /**
     * @param list<CouponOrderItem> $eligibleItems
     * @param list<array{sku_id?: string|int, spu_id?: string|int, quantity: int}> $requirements
     */
    public function calculateBuyGiftSets(array $eligibleItems, array $requirements): int
    {
        $useSpuLevel = $this->shouldUseSpuLevel($requirements);
        $quantityByProductId = $this->buildQuantityMap($eligibleItems, $useSpuLevel);
        $sets = $this->calculateMinSets($requirements, $quantityByProductId, $useSpuLevel);

        return PHP_INT_MAX === $sets ? 0 : $sets;
    }

    /**
     * 检测是否应该使用 SPU 级别的对比
     *
     * @param list<array{sku_id?: string|int, spu_id?: string|int, quantity: int}> $requirements
     */
    private function shouldUseSpuLevel(array $requirements): bool
    {
        foreach ($requirements as $requirement) {
            if (isset($requirement['spu_id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 构建商品数量映射表
     *
     * @param list<CouponOrderItem> $eligibleItems
     * @return array<string, int>
     */
    private function buildQuantityMap(array $eligibleItems, bool $useSpuLevel): array
    {
        $quantityByProductId = [];
        foreach ($eligibleItems as $item) {
            $productId = $useSpuLevel ? $item->getSpuId() : $item->getSkuId();

            if (null !== $productId) {
                $quantityByProductId[$productId] = ($quantityByProductId[$productId] ?? 0) + $item->getQuantity();
            }
        }

        return $quantityByProductId;
    }

    /**
     * 计算最小满足套数
     *
     * @param list<array{sku_id?: string|int, spu_id?: string|int, quantity: int}> $requirements
     * @param array<string, int> $quantityByProductId
     */
    private function calculateMinSets(array $requirements, array $quantityByProductId, bool $useSpuLevel): int
    {
        $sets = PHP_INT_MAX;
        foreach ($requirements as $requirement) {
            $requiredQty = max(1, $requirement['quantity']);

            if ($useSpuLevel && isset($requirement['spu_id'])) {
                $productId = (string) $requirement['spu_id'];
            } else {
                $productId = (string) ($requirement['sku_id'] ?? '');
            }

            $available = $quantityByProductId[$productId] ?? 0;
            $sets = min($sets, intdiv($available, $requiredQty));
        }

        return $sets;
    }
}
