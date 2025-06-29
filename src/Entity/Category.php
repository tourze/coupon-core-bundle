<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\CouponCoreBundle\Repository\CategoryRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;

#[ORM\Table(name: 'coupon_category', options: ['comment' => '优惠券分类表'])]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements \Stringable, Itemable, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 60, options: ['comment' => '分类名'])]
    private string $title;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Category $parent = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'LOGO地址'])]
    private ?string $logoUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '简介'])]
    private ?string $description = null;

    /**
     * 下级分类列表.
     *
     * @var Collection<Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'parent')]
    private Collection $children;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '显示标签'])]
    private ?array $showTags = [];

    #[Ignore]
    #[ORM\OneToMany(targetEntity: Coupon::class, mappedBy: 'category')]
    private Collection $coupons;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始有效时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '截止有效时间'])]
    private ?\DateTimeImmutable $endTime = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => '0', 'comment' => '次序值，order值大的排序靠前。有效的值范围是[0, 2^32]'])]
    private ?int $sortNumber = 0;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->coupons = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null || $this->getId() === 0) {
            return '';
        }

        return "#{$this->getId()} {$this->getTitle()}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<Category>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): self
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function toSelectItem(): array
    {
        return [
            'label' => "#{$this->getId()} {$this->getTitle()}",
            'text' => "#{$this->getId()} {$this->getTitle()}",
            'value' => $this->getId(),
        ];
    }

    public function getShowTags(): ?array
    {
        return $this->showTags;
    }

    public function setShowTags(?array $showTags): self
    {
        $this->showTags = $showTags;

        return $this;
    }

    public function getNestTitle(): string
    {
        if ($this->getParent() !== null) {
            return "{$this->getParent()->getTitle()}/{$this->getTitle()}";
        }

        return "{$this->getTitle()}";
    }

    public function getSimpleArray(): array
    {
        $result = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'logoUrl' => $this->getLogoUrl(),
            'valid' => $this->isValid(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'children' => [],
        ];

        $children = $this->getChildren()->toArray();
        usort($children, fn($a, $b) => ($b->getSortNumber() ?? 0) <=> ($a->getSortNumber() ?? 0));
        foreach ($children as $child) {
            /* @var static $child */
            $result['children'][] = $child->getSimpleArray();
        }

        return $result;
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    public function addCoupon(Coupon $coupon): static
    {
        if (!$this->coupons->contains($coupon)) {
            $this->coupons->add($coupon);
            $coupon->setCategory($this);
        }

        return $this;
    }

    public function removeCoupon(Coupon $coupon): static
    {
        if ($this->coupons->removeElement($coupon)) {
            // set the owning side to null (unless already changed)
            if ($coupon->getCategory() === $this) {
                $coupon->setCategory(null);
            }
        }

        return $this;
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'title' => $this->getTitle(),
            'logoUrl' => $this->getLogoUrl(),
            'description' => $this->getDescription(),
            'showTags' => $this->getShowTags(),
            ...$this->retrieveSortableArray(),
            'valid' => $this->isValid(),
            'startTime' => $this->getStartTime(),
            'endTime' => $this->getEndTime(),
        ];
    }

    public function retrieveReadArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'logoUrl' => $this->getLogoUrl(),
            'valid' => $this->isValid(),
            'startTime' => $this->getStartTime(),
            'endTime' => $this->getEndTime(),
        ];
    }

    public function retrieveSortableArray(): array
    {
        return [
            'sortNumber' => $this->getSortNumber(),
        ];
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): static
    {
        if ($startTime === null) {
            $this->startTime = null;
        } elseif ($startTime instanceof \DateTimeImmutable) {
            $this->startTime = $startTime;
        } else {
            $this->startTime = \DateTimeImmutable::createFromInterface($startTime);
        }

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        if ($endTime === null) {
            $this->endTime = null;
        } elseif ($endTime instanceof \DateTimeImmutable) {
            $this->endTime = $endTime;
        } else {
            $this->endTime = \DateTimeImmutable::createFromInterface($endTime);
        }

        return $this;
    }

    public function getSortNumber(): ?int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(?int $sortNumber): self
    {
        $this->sortNumber = $sortNumber;

        return $this;
    }}
