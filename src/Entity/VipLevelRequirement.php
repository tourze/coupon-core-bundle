<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * VIP等级限制条件
 */
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_vip_level')]
class VipLevelRequirement extends BaseRequirement
{
    #[ORM\Column(type: Types::INTEGER)]
    private int $minLevel = 1;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxLevel = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $allowedLevels = null;

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
        ];
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
