<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponContracts\CodeInterface;
use Tourze\CouponCoreBundle\Enum\CodeStatus;
use Tourze\CouponCoreBundle\Repository\CodeRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
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

    #[ORM\ManyToOne(targetEntity: Coupon::class, inversedBy: 'codes', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coupon $coupon = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '券码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $sn = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '领取时间'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeImmutable $gatherTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间, 必须在过期时间内才能使用'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeImmutable $expireTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '使用时间'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeImmutable $useTime = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $owner = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1, 'comment' => '核销次数, 保留字段，用于后续实现单优惠券多次核销的逻辑'])]
    #[Assert\PositiveOrZero]
    private ?int $consumeCount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否需要激活'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $needActive = null;

    #[ORM\Column(nullable: true, options: ['comment' => '是否已激活'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeImmutable $activeTime = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否锁定', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $locked = false;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    public function __toString(): string
    {
        return "#{$this->getId()} {$this->getSn()}";
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): void
    {
        $this->coupon = $coupon;
    }

    public function getGatherTime(): ?\DateTimeImmutable
    {
        return $this->gatherTime;
    }

    public function setGatherTime(?\DateTimeInterface $gatherTime): void
    {
        if (null === $gatherTime) {
            $this->gatherTime = null;
        } elseif ($gatherTime instanceof \DateTimeImmutable) {
            $this->gatherTime = $gatherTime;
        } else {
            $this->gatherTime = \DateTimeImmutable::createFromInterface($gatherTime);
        }
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): void
    {
        if (null === $expireTime) {
            $this->expireTime = null;
        } elseif ($expireTime instanceof \DateTimeImmutable) {
            $this->expireTime = $expireTime;
        } else {
            $this->expireTime = \DateTimeImmutable::createFromInterface($expireTime);
        }
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): void
    {
        $this->owner = $owner;
    }

    public function getUseTime(): ?\DateTimeImmutable
    {
        return $this->useTime;
    }

    public function setUseTime(?\DateTimeInterface $useTime): void
    {
        if (null === $useTime) {
            $this->useTime = null;
        } elseif ($useTime instanceof \DateTimeImmutable) {
            $this->useTime = $useTime;
        } else {
            $this->useTime = \DateTimeImmutable::createFromInterface($useTime);
        }
    }

    public function getSn(): ?string
    {
        return $this->sn;
    }

    public function setSn(string $sn): void
    {
        $this->sn = $sn;
    }

    public function getConsumeCount(): ?int
    {
        return $this->consumeCount;
    }

    public function setConsumeCount(int $consumeCount): void
    {
        $this->consumeCount = $consumeCount;
    }

    /**
     * @return array<string, int|string|null>
     */
    public function getQrcodeLink(): array
    {
        return [
            'code' => $this->getSn(),
            'sn' => $this->getSn(),
            't' => time() + 86400 * 30,
        ];
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getValidPeriodText(): ?string
    {
        if (null === $this->getExpireTime()) {
            return null;
        }

        if (null === $this->getGatherTime()) {
            return "有效期:至{$this->getExpireTime()->format('Y.m.d')}";
        }

        return "有效期:{$this->getGatherTime()->format('Y.m.d')}至{$this->getExpireTime()->format('Y.m.d')}";
    }

    public function isNeedActive(): ?bool
    {
        return $this->needActive;
    }

    public function setNeedActive(?bool $needActive): void
    {
        $this->needActive = $needActive;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    public function getActiveTime(): ?\DateTimeImmutable
    {
        return $this->activeTime;
    }

    public function setActiveTime(?\DateTimeInterface $activeTime): void
    {
        if (null === $activeTime) {
            $this->activeTime = null;
        } elseif ($activeTime instanceof \DateTimeImmutable) {
            $this->activeTime = $activeTime;
        } else {
            $this->activeTime = \DateTimeImmutable::createFromInterface($activeTime);
        }
    }

    public function getStatus(): CodeStatus
    {
        if (null !== $this->getUseTime()) {
            return CodeStatus::USED;
        }

        $coupon = $this->getCoupon();
        if (null === $coupon || true !== $coupon->isValid()) {
            return CodeStatus::INVALID;
        }

        $now = new \DateTimeImmutable();
        if (null !== $this->getExpireTime() && $now > $this->getExpireTime()) {
            return CodeStatus::EXPIRED;
        }

        $isValid = $this->isValid();
        if (false === $isValid || null === $isValid) {
            return CodeStatus::INVALID;
        }

        return CodeStatus::UNUSED;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'sn' => $this->getSn(),
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
            'owner' => $this->getOwner()?->getUserIdentifier(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return array_merge($this->retrieveApiArray(), [
            'created_by' => $this->getCreatedBy(),
            'updated_by' => $this->getUpdatedBy(),
            'create_time' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'update_time' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(?bool $locked): void
    {
        $this->locked = $locked;
    }
}
