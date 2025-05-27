<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\ConditionSystemBundle\Entity\BaseCondition;
use Tourze\ConditionSystemBundle\Enum\ConditionTrigger;
use Tourze\ConditionSystemBundle\Interface\SubjectInterface;
use Tourze\CouponCoreBundle\Adapter\CouponSubject;
use Tourze\CouponCoreBundle\Repository\GatherCountLimitConditionRepository;

/**
 * 领取次数限制条件
 */
#[ORM\Entity(repositoryClass: GatherCountLimitConditionRepository::class)]
#[ORM\Table(name: 'coupon_condition_gather_count_limit', options: ['comment' => '优惠券领取次数限制条件表'])]
class GatherCountLimitCondition extends BaseCondition
{
    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Coupon $coupon;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最大领取次数'])]
    private int $maxCount;

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
            'maxCount' => $this->maxCount,
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

    public function getMaxCount(): int
    {
        return $this->maxCount;
    }

    public function setMaxCount(int $maxCount): self
    {
        $this->maxCount = $maxCount;
        return $this;
    }
} 