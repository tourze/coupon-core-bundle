<?php

namespace Tourze\CouponCoreBundle\Entity;

use BenefitBundle\Model\BenefitResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponContracts\CouponInterface;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\EnumExtra\Itemable;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

#[AsPermission(title: '优惠券')]
#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupon_main', options: ['comment' => '优惠券'])]
class Coupon implements \Stringable, Itemable, AdminArrayInterface, ApiArrayInterface, BenefitResource, ResourceIdentity, CouponInterface
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[SnowflakeColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '唯一编码'])]
    private ?string $sn = null;

    #[ListColumn(title: '分类')]
    #[FormField(title: '分类')]
    #[ORM\ManyToOne(inversedBy: 'coupons')]
    private ?Category $category = null;

    /**
     * @var Collection<Code>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Code::class, mappedBy: 'coupon', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $codes;

    #[FormField(span: 12)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[ListColumn(sorter: true)]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '领取后过期天数'])]
    private ?int $expireDay = null;

    #[FormField(span: 5)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'ICON图标'])]
    private ?string $iconImg = null;

    #[FormField(span: 5)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '列表背景'])]
    private ?string $backImg = null;

    /**
     * @var Collection<Discount>
     */
    #[FormField(title: '优惠信息')]
    #[ORM\OneToMany(targetEntity: Discount::class, mappedBy: 'coupon', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $discounts;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '可用开始时间'])]
    private ?\DateTime $startDateTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '可用结束时间'])]
    private ?\DateTime $endDateTime = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否需要激活'])]
    private ?bool $needActive = null;

    #[ORM\Column(nullable: true, options: ['comment' => '激活后有效天数'])]
    private ?int $activeValidDay = null;

    /**
     * @var Collection<Attribute>
     */
    #[FormField(title: '属性')]
    #[ORM\OneToMany(targetEntity: Attribute::class, mappedBy: 'coupon', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true, indexBy: 'name')]
    private Collection $attributes;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '使用说明'])]
    private ?string $useDesc = null;

    #[ORM\OneToMany(targetEntity: CouponChannel::class, mappedBy: 'coupon')]
    private Collection $couponChannels;

    #[FormField(title: '渠道')]
    #[ORM\JoinTable(name: 'coupon_main_channel_relations')]
    #[ORM\ManyToMany(targetEntity: Channel::class, inversedBy: 'coupons', fetch: 'EXTRA_LAZY')]
    private Collection $channels;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: Batch::class, mappedBy: 'coupon')]
    private Collection $batches;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '开始有效时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '截止有效时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    #[IndexColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]#[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]public function __construct()
    {
        $this->codes = new ArrayCollection();
        $this->discounts = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->couponChannels = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->batches = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getName()}";
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
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

    /**
     * @return Collection<int, Code>
     */
    public function getCodes(): Collection
    {
        return $this->codes;
    }

    public function addCode(Code $code): self
    {
        if (!$this->codes->contains($code)) {
            $this->codes[] = $code;
            $code->setCoupon($this);
        }

        return $this;
    }

    public function removeCode(Code $code): self
    {
        if ($this->codes->removeElement($code)) {
            // set the owning side to null (unless already changed)
            if ($code->getCoupon() === $this) {
                $code->setCoupon(null);
            }
        }

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

    public function getExpireDay(): ?int
    {
        return $this->expireDay;
    }

    public function setExpireDay(int $expireDay): self
    {
        $this->expireDay = $expireDay;

        return $this;
    }

    /**
     * @return Collection<int, Discount>
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function addDiscount(Discount $discount): self
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts[] = $discount;
            $discount->setCoupon($this);
        }

        return $this;
    }

    public function removeDiscount(Discount $discount): self
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getCoupon() === $this) {
                $discount->setCoupon(null);
            }
        }

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

    #[ListColumn(title: '券码数量')]
    public function renderCodeCount(): int
    {
        return $this->getCodes()->count();
    }

    public function getStartDateTime(): ?\DateTime
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(?\DateTime $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?\DateTime
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(?\DateTime $endDateTime): self
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    /**
     * @return Collection<int, Attribute>
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(Attribute $attribute, ?string $key = null): self
    {
        if (!$this->attributes->contains($attribute)) {
            if (null !== $key) {
                $this->attributes[$key] = $attribute;
            } else {
                $this->attributes[] = $attribute;
            }

            $attribute->setCoupon($this);
        }

        return $this;
    }

    public function removeAttribute(Attribute $attribute): self
    {
        if ($this->attributes->removeElement($attribute)) {
            // set the owning side to null (unless already changed)
            if ($attribute->getCoupon() === $this) {
                $attribute->setCoupon(null);
            }
        }

        return $this;
    }

    public function getIconImg(): ?string
    {
        return $this->iconImg ?: ($_ENV['COUPON_DEFAULT_ICON_IMG'] ?? null);
    }

    public function setIconImg(?string $iconImg): self
    {
        $this->iconImg = $iconImg;

        return $this;
    }

    public function getBackImg(): ?string
    {
        return $this->backImg;
    }

    public function setBackImg(?string $backImg): self
    {
        $this->backImg = $backImg;

        return $this;
    }

    public function toSelectItem(): array
    {
        return [
            'label' => "{$this->getId()} {$this->getName()}",
            'text' => "{$this->getId()} {$this->getName()}",
            'value' => $this->getId(),
            'name' => $this->getName(),
        ];
    }

    public function getUseDesc(): ?string
    {
        return $this->useDesc;
    }

    public function setUseDesc(?string $useDesc): self
    {
        $this->useDesc = $useDesc;

        return $this;
    }

    public function isNeedActive(): ?bool
    {
        return $this->needActive;
    }

    public function setNeedActive(?bool $needActive): self
    {
        $this->needActive = $needActive;

        return $this;
    }

    public function getActiveValidDay(): ?int
    {
        return $this->activeValidDay;
    }

    public function setActiveValidDay(?int $activeValidDay): self
    {
        $this->activeValidDay = $activeValidDay;

        return $this;
    }

    /**
     * @return Collection<int, CouponChannel>
     */
    public function getCouponChannels(): Collection
    {
        return $this->couponChannels;
    }

    public function addCouponChannel(CouponChannel $couponChannel): self
    {
        if (!$this->couponChannels->contains($couponChannel)) {
            $this->couponChannels->add($couponChannel);
            $couponChannel->setCoupon($this);
        }

        return $this;
    }

    public function removeCouponChannel(CouponChannel $couponChannel): self
    {
        if ($this->couponChannels->removeElement($couponChannel)) {
            // set the owning side to null (unless already changed)
            if ($couponChannel->getCoupon() === $this) {
                $couponChannel->setCoupon(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Channel>
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): self
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }

        return $this;
    }

    public function removeChannel(Channel $channel): self
    {
        $this->channels->removeElement($channel);

        return $this;
    }

    /**
     * @return Collection<int, Batch>
     */
    public function getBatches(): Collection
    {
        return $this->batches;
    }

    public function addBatch(Batch $batch): self
    {
        if (!$this->batches->contains($batch)) {
            $this->batches->add($batch);
            $batch->setCoupon($this);
        }

        return $this;
    }

    public function removeBatch(Batch $batch): self
    {
        if ($this->batches->removeElement($batch)) {
            // set the owning side to null (unless already changed)
            if ($batch->getCoupon() === $this) {
                $batch->setCoupon(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'sn' => $this->getSn(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'name' => $this->getName(),
            'expireDay' => $this->getExpireDay(),
            'iconImg' => $this->getIconImg(),
            'backImg' => $this->getBackImg(),
            'discounts' => $this->getDiscounts(),
            'remark' => $this->getRemark(),
            'needActive' => $this->isNeedActive(),
            'activeValidDay' => $this->getActiveValidDay(),
            'useDesc' => $this->getUseDesc(),
            'startTime' => $this->getStartTime(),
            'endTime' => $this->getEndTime(),
        ];
    }

    public function retrieveApiArray(): array
    {
        $discounts = [];
        foreach ($this->getDiscounts() as $discount) {
            $discounts[] = $discount->retrieveApiArray();
        }

        $attributes = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributes[] = $attribute->retrieveApiArray();
        }

        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'startDateTime' => $this->getStartDateTime()?->format('Y-m-d H:i:s'),
            'endDateTime' => $this->getEndDateTime()?->format('Y-m-d H:i:s'),
            'sn' => $this->getSn(),
            'name' => $this->getName(),
            'expireDay' => $this->getExpireDay(),
            'iconImg' => $this->getIconImg(),
            'discounts' => $discounts,
            'remark' => $this->getRemark(),
            'attributes' => $attributes,
            'startTime' => $this->getStartTime(),
            'endTime' => $this->getEndTime(),
        ];
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getResourceId(): string
    {
        return $this->getId();
    }

    public function getResourceLabel(): string
    {
        return (string) $this->getName();
    }}
