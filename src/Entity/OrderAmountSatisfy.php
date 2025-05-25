<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 订单金额限制条件
 */
#[ORM\Entity]
#[ORM\Table(name: 'coupon_satisfy_order_amount')]
class OrderAmountSatisfy extends BaseSatisfy
{
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $minAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $maxAmount = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $excludeCategories = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $includeCategories = null;

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'label' => $this->getLabel(),
            'minAmount' => $this->minAmount,
            'maxAmount' => $this->maxAmount,
            'excludeCategories' => $this->excludeCategories,
            'includeCategories' => $this->includeCategories,
            'enabled' => $this->isEnabled(),
            'remark' => $this->getRemark(),
        ];
    }

    public function getMinAmount(): string
    {
        return $this->minAmount;
    }

    public function setMinAmount(string $minAmount): self
    {
        $this->minAmount = $minAmount;
        return $this;
    }

    public function getMaxAmount(): ?string
    {
        return $this->maxAmount;
    }

    public function setMaxAmount(?string $maxAmount): self
    {
        $this->maxAmount = $maxAmount;
        return $this;
    }

    public function getExcludeCategories(): ?array
    {
        return $this->excludeCategories;
    }

    public function setExcludeCategories(?array $excludeCategories): self
    {
        $this->excludeCategories = $excludeCategories;
        return $this;
    }

    public function getIncludeCategories(): ?array
    {
        return $this->includeCategories;
    }

    public function setIncludeCategories(?array $includeCategories): self
    {
        $this->includeCategories = $includeCategories;
        return $this;
    }
} 