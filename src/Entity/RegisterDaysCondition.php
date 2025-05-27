<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\ConditionSystemBundle\Entity\BaseCondition;
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;
use Tourze\CouponCoreBundle\Repository\RegisterDaysConditionRepository;

/**
 * 注册天数限制条件
 */
#[ORM\Entity(repositoryClass: RegisterDaysConditionRepository::class)]
#[ORM\Table(name: 'coupon_condition_register_days', options: ['comment' => '优惠券注册天数条件表'])]
class RegisterDaysCondition extends BaseCondition
{
    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Coupon $coupon;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最少注册天数'])]
    private int $minDays = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '最多注册天数'])]
    private ?int $maxDays = null;

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
            'minDays' => $this->minDays,
            'maxDays' => $this->maxDays,
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

    public function getMinDays(): int
    {
        return $this->minDays;
    }

    public function setMinDays(int $minDays): self
    {
        $this->minDays = $minDays;
        return $this;
    }

    public function getMaxDays(): ?int
    {
        return $this->maxDays;
    }

    public function setMaxDays(?int $maxDays): self
    {
        $this->maxDays = $maxDays;
        return $this;
    }
} 