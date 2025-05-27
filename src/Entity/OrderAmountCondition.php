<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\ConditionSystemBundle\Entity\BaseCondition;
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;
use Tourze\CouponCoreBundle\Repository\OrderAmountConditionRepository;

/**
 * 订单金额限制条件
 */
#[ORM\Entity(repositoryClass: OrderAmountConditionRepository::class)]
#[ORM\Table(name: 'coupon_condition_order_amount', options: ['comment' => '优惠券订单金额条件表'])]
class OrderAmountCondition extends BaseCondition
{
    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Coupon $coupon;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '最低订单金额'])]
    private string $minAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '最高订单金额'])]
    private ?string $maxAmount = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '包含商品分类'])]
    private ?array $includeCategories = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '排除商品分类'])]
    private ?array $excludeCategories = null;

    public function getTrigger(): ConditionTrigger
    {
        return ConditionTrigger::VALIDATION;
    }

    public function getSubject(): ?SubjectInterface
    {
        return new CouponSubject($this->coupon);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'label' => $this->getLabel(),
            'minAmount' => $this->minAmount,
            'maxAmount' => $this->maxAmount,
            'includeCategories' => $this->includeCategories,
            'excludeCategories' => $this->excludeCategories,
            'enabled' => $this->isEnabled(),
            'remark' => $this->getRemark(),
            'trigger' => $this->getTrigger()->value,
        ];
    }

    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(Coupon $coupon): self
    {
        $this->coupon = $coupon;
        return $this;
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

    public function getIncludeCategories(): ?array
    {
        return $this->includeCategories;
    }

    public function setIncludeCategories(?array $includeCategories): self
    {
        $this->includeCategories = $includeCategories;
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
} 