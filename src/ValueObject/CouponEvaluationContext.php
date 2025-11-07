<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 优惠券计算上下文
 *
 * @psalm-type ContextMetadata = array<string, mixed>
 */
class CouponEvaluationContext
{
    /**
     * @param list<CouponOrderItem> $items
     * @param ContextMetadata $metadata
     */
    public function __construct(
        private readonly ?UserInterface $user,
        private readonly array $items,
        private readonly array $metadata = [],
        private readonly \DateTimeImmutable $evaluateTime = new \DateTimeImmutable(),
    ) {
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @return list<CouponOrderItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getEvaluateTime(): \DateTimeImmutable
    {
        return $this->evaluateTime;
    }

    /**
     * @return ContextMetadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * 过滤出符合范围的订单项目
     *
     * @param CouponScopeVO $scope
     * @return list<CouponOrderItem>
     */
    public function filterItemsByScope(CouponScopeVO $scope): array
    {
        return array_values(array_filter(
            $this->items,
            static function (CouponOrderItem $item) use ($scope): bool {
                if (!$item->isSelected()) {
                    return false;
                }
                if (!$scope->isSkuEligible($item->getSkuId(), $item->getGtin(), $item->getSpuGtin())) {
                    return false;
                }
                if (!$scope->isSpuEligible($item->getSpuId(), $item->getSpuGtin())) {
                    return false;
                }
                if (!$scope->isCategoryEligible($item->getCategoryId())) {
                    return false;
                }

                return true;
            }
        ));
    }

    /**
     * 计算订单项目总金额
     *
     * @param list<CouponOrderItem> $items
     * @return numeric-string
     */
    public function calculateItemsTotal(array $items): string
    {
        $total = '0.00';
        foreach ($items as $item) {
            $total = bcadd($total, $item->getSubtotal(), 2);
        }

        return $total;
    }

    /**
     * 验证是否满足必需SPU条件
     * 
     * @param list<string> $requiredSpuIds
     */
    public function hasRequiredSpus(array $requiredSpuIds): bool
    {
        if ([] === $requiredSpuIds) {
            return true; // 没有必需SPU要求
        }

        // 收集用户购买的所有SPU ID
        $purchasedSpuIds = [];
        foreach ($this->items as $item) {
            if ($item->isSelected() && $item->getSpuId() !== null) {
                $purchasedSpuIds[] = $item->getSpuId();
            }
        }

        $purchasedSpuIds = array_unique($purchasedSpuIds);

        // 检查是否包含所有必需的SPU
        foreach ($requiredSpuIds as $requiredSpuId) {
            if (!in_array($requiredSpuId, $purchasedSpuIds, true)) {
                return false; // 缺少必需的SPU
            }
        }

        return true; // 包含所有必需的SPU
    }

    /**
     * 创建包含新元数据的上下文副本
     *
     * @param ContextMetadata $metadata
     */
    public function withMetadata(array $metadata): self
    {
        return new self(
            $this->user,
            $this->items,
            array_merge($this->metadata, $metadata),
            $this->evaluateTime
        );
    }
}
