<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\ConditionSystemBundle\Entity\BaseCondition;

/**
 * 简化的测试用优惠券实体
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_coupon')]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $sn = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<BaseCondition>
     */
    #[ORM\OneToMany(targetEntity: BaseCondition::class, mappedBy: 'coupon', cascade: ['persist'], orphanRemoval: true)]
    private Collection $conditions;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSn(): ?string
    {
        return $this->sn;
    }

    public function setSn(?string $sn): self
    {
        $this->sn = $sn;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, BaseCondition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(BaseCondition $condition): self
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
        }

        return $this;
    }

    public function removeCondition(BaseCondition $condition): self
    {
        $this->conditions->removeElement($condition);
        return $this;
    }
} 