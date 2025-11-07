<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Service\Evaluator\Helper;

use Tourze\CouponCoreBundle\Enum\AllocationRule;
use Tourze\CouponCoreBundle\ValueObject\CouponOrderItem;

/**
 * @internal
 */
class DiscountAllocator
{
    /**
     * @param list<CouponOrderItem> $items
     * @param list<string> $prioritySkuIds
     * @return list<array{sku_id: string, amount: numeric-string}>
     */
    public function allocate(AllocationRule $rule, array $items, string $discount, array $prioritySkuIds = []): array
    {
        $discountAmount = $this->toNumericString($discount);
        /** @var numeric-string $discountAmount */
        $discountAmount = $discountAmount;

        return match ($rule) {
            AllocationRule::AVERAGE => $this->allocateAverage($items, $discountAmount),
            AllocationRule::PRIORITY => $this->allocatePriority($items, $discountAmount, $prioritySkuIds),
            AllocationRule::PROPORTIONAL => $this->allocateProportional($items, $discountAmount),
        };
    }

    /**
     * @param list<CouponOrderItem> $items
     * @param numeric-string $discount
     * @return list<array{sku_id: string, amount: numeric-string}>
     */
    private function allocateProportional(array $items, string $discount): array
    {
        /** @var numeric-string $total */
        $total = '0.00';
        foreach ($items as $item) {
            $total = bcadd($total, $item->getSubtotal(), 6);
        }

        if (bccomp($total, '0.00', 6) <= 0) {
            return [];
        }

        $allocations = [];
        /** @var numeric-string $allocated */
        $allocated = '0.00';
        $count = count($items);
        foreach ($items as $index => $item) {
            $amount = $index === $count - 1
                ? $this->subtractAmount($discount, $allocated)
                : $this->calculateProportionalShare($discount, $total, $item->getSubtotal());
            /** @var numeric-string $amount */
            $amount = $amount;
            $allocated = bcadd($allocated, $amount, 2);
            $allocations[] = [
                'sku_id' => $item->getSkuId(),
                'amount' => $amount,
            ];
        }

        return $allocations;
    }

    /**
     * @param list<CouponOrderItem> $items
     * @param numeric-string $discount
     * @return list<array{sku_id: string, amount: numeric-string}>
     */
    private function allocateAverage(array $items, string $discount): array
    {
        $count = count($items);
        if (0 === $count) {
            return [];
        }

        $base = bcdiv($discount, sprintf('%.0f', $count), 2);
        $allocations = [];
        /** @var numeric-string $allocated */
        $allocated = '0.00';

        foreach ($items as $index => $item) {
            $amount = $index === $count - 1
                ? $this->subtractAmount($discount, $allocated)
                : (bccomp($base, $item->getSubtotal(), 2) > 0 ? $item->getSubtotal() : $base);
            /** @var numeric-string $amount */
            $amount = $amount;
            $allocated = bcadd($allocated, $amount, 2);
            $allocations[] = [
                'sku_id' => $item->getSkuId(),
                'amount' => $amount,
            ];
        }

        return $allocations;
    }

    /**
     * @param list<CouponOrderItem> $items
     * @param list<string> $prioritySkuIds
     * @param numeric-string $discount
     * @return list<array{sku_id: string, amount: numeric-string}>
     */
    private function allocatePriority(array $items, string $discount, array $prioritySkuIds): array
    {
        /** @var numeric-string $remaining */
        $remaining = $discount;
        $allocations = [];

        $itemsMap = [];
        foreach ($items as $item) {
            $itemsMap[$item->getSkuId()] = $item;
        }

        $priorityResult = $this->allocateForSkuList($prioritySkuIds, $itemsMap, $remaining);
        $allocations = $priorityResult['allocations'];
        $remaining = $priorityResult['remaining'];

        if (bccomp($remaining, '0.00', 2) > 0) {
            $nonPriority = array_values(array_diff(array_keys($itemsMap), $prioritySkuIds));
            $fallbackResult = $this->allocateForSkuList($nonPriority, $itemsMap, $remaining);
            $allocations = array_merge($allocations, $fallbackResult['allocations']);
            $remaining = $fallbackResult['remaining'];
        }

        return $allocations;
    }

    /**
     * @param list<string> $skuIds
     * @param array<string, CouponOrderItem> $itemsMap
     * @param numeric-string $remaining
     * @return array{allocations: list<array{sku_id: string, amount: numeric-string}>, remaining: numeric-string}
     */
    private function allocateForSkuList(array $skuIds, array $itemsMap, string $remaining): array
    {
        $allocations = [];
        foreach ($skuIds as $skuId) {
            $item = $itemsMap[$skuId] ?? null;
            if (null === $item) {
                continue;
            }

            $available = $item->getSubtotal();
            $amount = bccomp($available, $remaining, 2) >= 0 ? $remaining : $available;
            /** @var numeric-string $amount */
            $amount = $amount;
            if (bccomp($amount, '0.00', 2) <= 0) {
                continue;
            }

            $allocations[] = [
                'sku_id' => $item->getSkuId(),
                'amount' => $amount,
            ];

            $remaining = $this->subtractAmount($remaining, $amount);
            if (bccomp($remaining, '0.00', 2) <= 0) {
                break;
            }
        }

        /** @var numeric-string $remaining */
        $remaining = $remaining;

        return [
            'allocations' => $allocations,
            'remaining' => $remaining,
        ];
    }

    /**
     * @param numeric-string $discount
     * @param numeric-string $total
     * @param numeric-string $itemSubtotal
     * @return numeric-string
     */
    private function calculateProportionalShare(string $discount, string $total, string $itemSubtotal): string
    {
        $ratio = bcdiv($itemSubtotal, $total, 8);
        $share = bcmul($discount, $ratio, 2);

        if (bccomp($share, $itemSubtotal, 2) > 0) {
            return $itemSubtotal;
        }

        return $share;
    }

    /**
     * @param numeric-string $left
     * @param numeric-string $right
     * @return numeric-string
     */
    private function subtractAmount(string $left, string $right): string
    {
        return bcsub($left, $right, 2);
    }

    /**
     * @return numeric-string
     */
    private function toNumericString(string|int|float $value): string
    {
        if (is_string($value) && is_numeric($value)) {
            return sprintf('%.2f', (float) $value);
        }

        return sprintf('%.2f', (float) $value);
    }
}
