<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\ConditionSystemBundle\Entity\BaseCondition;
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;
use Tourze\CouponCoreBundle\Repository\VipLevelConditionRepository;

/**
 * VIP等级限制条件
 */
#[ORM\Entity(repositoryClass: VipLevelConditionRepository::class)]
#[ORM\Table(name: 'coupon_condition_vip_level', options: ['comment' => '优惠券VIP等级条件表'])]
class VipLevelCondition extends BaseCondition
{
    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Coupon $coupon;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最低VIP等级'])]
    private int $minLevel = 1;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '最高VIP等级'])]
    private ?int $maxLevel = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '允许的VIP等级列表'])]
    private ?array $allowedLevels = null;

    public function getTrigger(): ConditionTrigger
    {
        return ConditionTrigger::BEFORE_ACTION;
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
            'minLevel' => $this->minLevel,
            'maxLevel' => $this->maxLevel,
            'allowedLevels' => $this->allowedLevels,
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

    public function getMinLevel(): int
    {
        return $this->minLevel;
    }

    public function setMinLevel(int $minLevel): self
    {
        $this->minLevel = $minLevel;
        return $this;
    }

    public function getMaxLevel(): ?int
    {
        return $this->maxLevel;
    }

    public function setMaxLevel(?int $maxLevel): self
    {
        $this->maxLevel = $maxLevel;
        return $this;
    }

    public function getAllowedLevels(): ?array
    {
        return $this->allowedLevels;
    }

    public function setAllowedLevels(?array $allowedLevels): self
    {
        $this->allowedLevels = $allowedLevels;
        return $this;
    }
} 