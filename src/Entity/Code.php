<?php

namespace Tourze\CouponCoreBundle\Entity;

use Carbon\CarbonImmutable;
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
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: CodeRepository::class)]
#[ORM\Table(name: 'coupon_code', options: ['comment' => '券码'])]
class Code implements \Stringable, AdminArrayInterface, ApiArrayInterface, CodeInterface
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '码ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: Coupon::class, inversedBy: 'codes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coupon $coupon = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'channels')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Channel $channel = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '券码'])]
    private ?string $sn = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Channel $gatherChannel = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Channel $useChannel = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '领取时间'])]
    private ?\DateTimeImmutable $gatherTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间, 必须在过期时间内才能使用'])]
    private ?\DateTimeImmutable $expireTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '使用时间'])]
    private ?\DateTimeImmutable $useTime = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $owner = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1, 'comment' => '核销次数, 保留字段，用于后续实现单优惠券多次核销的逻辑'])]
    private ?int $consumeCount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否需要激活'])]
    private ?bool $needActive = null;

    #[ORM\Column(nullable: true, options: ['comment' => '是否已激活'])]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    private ?\DateTimeImmutable $activeTime = null;

    #[Ignore]
    #[ORM\OneToOne(mappedBy: 'code', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?ReadStatus $readStatus = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否锁定', 'default' => 0])]
    private ?bool $locked = false;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function __toString(): string
    {
        return "#{$this->getId()} {$this->getSn()}";
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

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getGatherTime(): ?\DateTimeImmutable
    {
        return $this->gatherTime;
    }

    public function setGatherTime(?\DateTimeInterface $gatherTime): self
    {
        if ($gatherTime === null) {
            $this->gatherTime = null;
        } elseif ($gatherTime instanceof \DateTimeImmutable) {
            $this->gatherTime = $gatherTime;
        } else {
            $this->gatherTime = \DateTimeImmutable::createFromInterface($gatherTime);
        }

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): self
    {
        if ($expireTime === null) {
            $this->expireTime = null;
        } elseif ($expireTime instanceof \DateTimeImmutable) {
            $this->expireTime = $expireTime;
        } else {
            $this->expireTime = \DateTimeImmutable::createFromInterface($expireTime);
        }

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

    public function getUseTime(): ?\DateTimeImmutable
    {
        return $this->useTime;
    }

    public function setUseTime(?\DateTimeInterface $useTime): self
    {
        if ($useTime === null) {
            $this->useTime = null;
        } elseif ($useTime instanceof \DateTimeImmutable) {
            $this->useTime = $useTime;
        } else {
            $this->useTime = \DateTimeImmutable::createFromInterface($useTime);
        }

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

    public function getGatherChannel(): ?Channel
    {
        return $this->gatherChannel;
    }

    public function setGatherChannel(?Channel $gatherChannel): self
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
    public function getQrcodeLink(): array
    {
        $data = [
            'code' => $this->getSn(),
            'sn' => $this->getSn(),
            't' => time() + 86400 * 30,
        ];

        return $data;
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
        if ($this->getExpireTime() === null) {
            return null;
        }

        if ($this->getGatherTime() === null) {
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

    public function getActiveTime(): ?\DateTimeImmutable
    {
        return $this->activeTime;
    }

    public function setActiveTime(?\DateTimeInterface $activeTime): self
    {
        if ($activeTime === null) {
            $this->activeTime = null;
        } elseif ($activeTime instanceof \DateTimeImmutable) {
            $this->activeTime = $activeTime;
        } else {
            $this->activeTime = \DateTimeImmutable::createFromInterface($activeTime);
        }

        return $this;
    }

    public function getUseChannel(): ?Channel
    {
        return $this->useChannel;
    }

    public function setUseChannel(?Channel $useChannel): self
    {
        $this->useChannel = $useChannel;

        return $this;
    }

    public function getStatus(): CodeStatus
    {
        if ($this->getUseTime() !== null) {
            return CodeStatus::USED;
        }

        if (!$this->getCoupon()?->isValid()) {
            return CodeStatus::INVALID;
        }

        $now = CarbonImmutable::now();
        if ($this->getExpireTime() !== null && $now->greaterThan($this->getExpireTime())) {
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

    public function setChannel(?Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'sn' => $this->getSn(),
            'gather_channel' => $this->getGatherChannel()?->retrieveApiArray(),
            'use_channel' => $this->getUseChannel()?->retrieveApiArray(),
            'consume_count' => $this->getConsumeCount(),
            'valid' => $this->isValid(),
            'locked' => $this->isLocked(),
            'need_active' => $this->isNeedActive(),
            'active' => $this->isActive(),
            'gather_time' => $this->getGatherTime()?->format('Y-m-d H:i:s'),
            'expire_time' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'use_time' => $this->getUseTime()?->format('Y-m-d H:i:s'),
            'active_time' => $this->getActiveTime()?->format('Y-m-d H:i:s'),
            'remark' => $this->getRemark(),
            'status' => $this->getStatus()->value,
            'coupon' => $this->getCoupon()?->retrieveApiArray(),
            'channel' => $this->getChannel()?->retrieveApiArray(),
            'owner' => $this->getOwner()?->getUserIdentifier(),
            'read_status' => $this->getReadStatus()?->retrieveApiArray(),
        ];
    }

    public function retrieveAdminArray(): array
    {
        return array_merge($this->retrieveApiArray(), [
            'created_by' => $this->getCreatedBy(),
            'updated_by' => $this->getUpdatedBy(),
            'create_time' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'update_time' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ]);
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
    }}
