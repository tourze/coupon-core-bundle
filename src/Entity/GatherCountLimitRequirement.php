<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 领取次数限制条件
 */
#[ORM\Entity]
#[ORM\Table(name: 'coupon_requirement_gather_count_limit')]
class GatherCountLimitRequirement extends BaseRequirement
{
    #[ORM\Column(type: Types::INTEGER)]
    private int $maxCount;

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'label' => $this->getLabel(),
            'maxCount' => $this->maxCount,
            'remark' => $this->getRemark(),
            'enabled' => $this->isEnabled(),
        ];
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
