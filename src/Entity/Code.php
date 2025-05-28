<?php

namespace Tourze\CouponCoreBundle\Entity;

use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponContracts\CodeInterface;
use Tourze\CouponCoreBundle\Enum\CodeStatus;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Yiisoft\Json\Json;

#[AsPermission(title: '券码管理')]
#[Exportable]
#[ORM\Entity(repositoryClass: CodeRepository::class)]
#[ORM\Table(name: 'coupon_code', options: ['comment' => '券码'])]
class Code implements \Stringable, AdminArrayInterface, ApiArrayInterface, CodeInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '码ID'])]
    private ?int $id = 0;

    #[Filterable(label: '优惠券', inputWidth: 200)]
    #[ListColumn(title: '优惠券')]
    #[ExportColumn(title: '优惠券')]
    #[ORM\ManyToOne(targetEntity: Coupon::class, inversedBy: 'codes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coupon $coupon = null;

    #[Filterable(label: '渠道')]
    #[ListColumn(title: '渠道')]
    #[ExportColumn(title: '渠道')]
    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'channels')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Channel $channel = null;

    #[TrackColumn]
    #[ExportColumn(title: '券码')]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '券码'])]
    private ?string $sn = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '领取渠道'])]
    private ?string $gatherChannel = null;

    #[ORM\Column(length: 40, nullable: true, options: ['comment' => '使用渠道'])]
    private ?string $useChannel = null;

    #[ListColumn(sorter: true)]
    #[ExportColumn(title: '领取时间')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '领取时间'])]
    private ?\DateTimeInterface $gatherTime = null;

    /**
     * 必须在过期时间内才能使用喔.
     */
    #[ListColumn(sorter: true)]
    #[ExportColumn(title: '过期时间')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $expireTime = null;

    #[ListColumn(sorter: true)]
    #[ExportColumn(title: '使用时间')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '使用时间'])]
    private ?\DateTimeInterface $useTime = null;

    #[ListColumn(title: '拥有用户')]
    #[ExportColumn(title: '拥有用户')]
    #[ORM\ManyToOne(targetEntity: UserInterface::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $owner = null;

    /**
     * 保留字段，用于后续实现单优惠券多次核销的逻辑.
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1, 'comment' => '核销次数'])]
    private ?int $consumeCount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remark = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否需要激活'])]
    private ?bool $needActive = null;

    #[ORM\Column(nullable: true, options: ['comment' => '是否已激活'])]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    private ?\DateTimeInterface $activeTime = null;

    #[Ignore]
    #[ORM\OneToOne(mappedBy: 'code', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?ReadStatus $readStatus = null;

    #[BoolColumn]
    #[ListColumn(order: 97)]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否锁定', 'default' => 0])]
    private ?bool $locked = false;

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

    #[IndexColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function __toString(): string
    {
        return "#{$this->getId()} {$this->getSn()}";
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

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): self
    {
        $this->coupon = $coupon;

        return $this;
    }

    public function getGatherTime(): ?\DateTimeInterface
    {
        return $this->gatherTime;
    }

    public function setGatherTime(?\DateTimeInterface $gatherTime): self
    {
        $this->gatherTime = $gatherTime;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeInterface
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): self
    {
        $this->expireTime = $expireTime;

        return $this;
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getUseTime(): ?\DateTimeInterface
    {
        return $this->useTime;
    }

    public function setUseTime(?\DateTimeInterface $useTime): self
    {
        $this->useTime = $useTime;

        return $this;
    }

    public function getSn(): ?string
    {
        return $this->sn;
    }

    public function setSn(string $sn): self
    {
        $this->sn = $sn;

        return $this;
    }

    public function getGatherChannel(): ?string
    {
        return $this->gatherChannel;
    }

    public function setGatherChannel(?string $gatherChannel): self
    {
        $this->gatherChannel = $gatherChannel;

        return $this;
    }

    public function getConsumeCount(): ?int
    {
        return $this->consumeCount;
    }

    public function setConsumeCount(int $consumeCount): self
    {
        $this->consumeCount = $consumeCount;

        return $this;
    }

    /**
     * @throws \JsonException
     */
    public function getQrcodeLink(): string
    {
        return Json::encode([
            'code' => $this->getSn(),
            't' => time(),
        ]);
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

    public function getValidPeriodText(): ?string
    {
        if (!$this->getExpireTime()) {
            return null;
        }

        if (!$this->getGatherTime()) {
            return "有效期:至{$this->getExpireTime()->format('Y.m.d')}";
        }

        return "有效期:{$this->getGatherTime()->format('Y.m.d')}至{$this->getExpireTime()->format('Y.m.d')}";
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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getActiveTime(): ?\DateTimeInterface
    {
        return $this->activeTime;
    }

    public function setActiveTime(?\DateTimeInterface $activeTime): self
    {
        $this->activeTime = $activeTime;

        return $this;
    }

    public function getUseChannel(): ?string
    {
        return $this->useChannel;
    }

    public function setUseChannel(?string $useChannel): self
    {
        $this->useChannel = $useChannel;

        return $this;
    }

    public function getStatus(): CodeStatus
    {
        if ($this->getUseTime()) {
            return CodeStatus::USED;
        }

        if (!$this->getCoupon()?->isValid()) {
            return CodeStatus::INVALID;
        }

        $now = Carbon::now();
        if ($this->getExpireTime() && $now->greaterThan($this->getExpireTime())) {
            return CodeStatus::EXPIRED;
        }

        if (!$this->isValid()) {
            return CodeStatus::INVALID;
        }

        return CodeStatus::UNUSED;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): void
    {
        $this->channel = $channel;
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'sn' => $this->getSn(),
            'owner' => $this->getOwner()?->retrieveApiArray(),
            'coupon' => $this->getCoupon()?->retrieveAdminArray(),
            'gatherChannel' => $this->getGatherChannel(),
            'useChannel' => $this->getUseChannel(),
            'gatherTime' => $this->getGatherTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'useTime' => $this->getUseTime()?->format('Y-m-d H:i:s'),
            'locked' => $this->isLocked(),
        ];
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'useTime' => $this->getUseTime()?->format('Y-m-d H:i:s'),
            'activeTime' => $this->getActiveTime()?->format('Y-m-d H:i:s'),
            'coupon' => $this->getCoupon()?->retrieveApiArray(),
            'channel' => $this->getChannel()?->retrieveApiArray(),
            'owner' => $this->getOwner()?->retrieveApiArray(),
            'sn' => $this->getSn(),
            'gatherChannel' => $this->getGatherChannel(),
            'useChannel' => $this->getUseChannel(),
            'remark' => $this->getRemark(),
            'needActive' => $this->isNeedActive(),
            'active' => $this->isActive(),
            'status' => $this->getStatus()->value,
        ];
    }

    public function getReadStatus(): ?ReadStatus
    {
        return $this->readStatus;
    }

    public function setReadStatus(ReadStatus $readStatus): static
    {
        // set the owning side of the relation if necessary
        if ($readStatus->getCode() !== $this) {
            $readStatus->setCode($this);
        }

        $this->readStatus = $readStatus;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(?bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }
}
