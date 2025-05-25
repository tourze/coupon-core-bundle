<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 注册天数限制条件
 */
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_register_days')]
class RegisterDaysRequirement extends BaseRequirement
{
    #[ORM\Column(type: Types::INTEGER)]
    private int $minDays = 0;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxDays = null;

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
        ];
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