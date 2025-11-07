<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Entity;

use BenefitBundle\Model\BenefitResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponContracts\CouponInterface;
use Tourze\CouponCoreBundle\Enum\CouponType;
use Tourze\CouponCoreBundle\Repository\CouponRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

/**
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupon_main', options: ['comment' => '优惠券'])]
class Coupon implements \Stringable, Itemable, AdminArrayInterface, ApiArrayInterface, BenefitResource, ResourceIdentity, CouponInterface
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null; // @phpstan-ignore-line property.unusedType Doctrine sets this

    #[SnowflakeColumn]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '唯一编码'])]
    private ?string $sn = null;

    /**
     * @var Collection<int, Code>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Code::class, mappedBy: 'coupon', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $codes;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '优惠券类型', 'default' => 'full_reduction'])]
    private string $type = CouponType::FULL_REDUCTION->value;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '优惠券配置'])]
    private array $configuration = [];

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '领取后过期天数'])]
    private ?int $expireDay = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'ICON图标'])]
    private ?string $iconImg = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '列表背景'])]
    private ?string $backImg = null;

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '可用开始时间'])]
    private ?\DateTimeImmutable $startDateTime = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '可用结束时间'])]
    private ?\DateTimeImmutable $endDateTime = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否需要激活'])]
    private ?bool $needActive = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '激活后有效天数'])]
    private ?int $activeValidDay = null;

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '使用说明'])]
    private ?string $useDesc = null;

    /**
     * @var Collection<int, Batch>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Batch::class, mappedBy: 'coupon', cascade: ['persist'])]
    private Collection $batches;

    #[Ignore]
    #[ORM\OneToOne(targetEntity: CommandConfig::class, inversedBy: 'coupon')]
    #[ORM\JoinColumn(name: 'command_config_id', referencedColumnName: 'id', nullable: true)]
    private ?CommandConfig $commandConfig = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始有效时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '截止有效时间'])]
    private ?\DateTimeImmutable $endTime = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->codes = new ArrayCollection();
        $this->batches = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId() || 0 === $this->getId()) {
            return '';
        }

        return "{$this->getName()}";
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

    public function getSn(): ?string
    {
        return $this->sn;
    }

    public function setSn(?string $sn): void
    {
        $this->sn = $sn;
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
            $this->codes->add($code);
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): CouponType
    {
        return CouponType::tryFrom($this->type ?? CouponType::FULL_REDUCTION->value) ?? CouponType::FULL_REDUCTION;
    }

    public function setType(CouponType $type): void
    {
        $this->type = $type->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getExpireDay(): ?int
    {
        return $this->expireDay;
    }

    public function setExpireDay(int $expireDay): void
    {
        $this->expireDay = $expireDay;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getStartDateTime(): ?\DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(?\DateTimeInterface $startDateTime): void
    {
        if (null === $startDateTime) {
            $this->startDateTime = null;
        } elseif ($startDateTime instanceof \DateTimeImmutable) {
            $this->startDateTime = $startDateTime;
        } else {
            $this->startDateTime = \DateTimeImmutable::createFromInterface($startDateTime);
        }
    }

    public function getEndDateTime(): ?\DateTimeImmutable
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(?\DateTimeInterface $endDateTime): void
    {
        if (null === $endDateTime) {
            $this->endDateTime = null;
        } elseif ($endDateTime instanceof \DateTimeImmutable) {
            $this->endDateTime = $endDateTime;
        } else {
            $this->endDateTime = \DateTimeImmutable::createFromInterface($endDateTime);
        }
    }

    public function getIconImg(): ?string
    {
        if (null !== $this->iconImg) {
            return $this->iconImg;
        }

        $defaultIcon = $_ENV['COUPON_DEFAULT_ICON_IMG'] ?? null;

        return is_string($defaultIcon) ? $defaultIcon : null;
    }

    public function setIconImg(?string $iconImg): void
    {
        $this->iconImg = $iconImg;
    }

    public function getBackImg(): ?string
    {
        return $this->backImg;
    }

    public function setBackImg(?string $backImg): void
    {
        $this->backImg = $backImg;
    }

    /**
     * @return array<string, int|string|null>
     */
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

    public function setUseDesc(?string $useDesc): void
    {
        $this->useDesc = $useDesc;
    }

    public function isNeedActive(): ?bool
    {
        return $this->needActive;
    }

    public function setNeedActive(?bool $needActive): void
    {
        $this->needActive = $needActive;
    }

    public function getActiveValidDay(): ?int
    {
        return $this->activeValidDay;
    }

    public function setActiveValidDay(?int $activeValidDay): void
    {
        $this->activeValidDay = $activeValidDay;
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

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'sn' => $this->getSn(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'name' => $this->getName(),
            'type' => $this->getType()->value,
            'expireDay' => $this->getExpireDay(),
            'iconImg' => $this->getIconImg(),
            'backImg' => $this->getBackImg(),
            'remark' => $this->getRemark(),
            'needActive' => $this->isNeedActive(),
            'activeValidDay' => $this->getActiveValidDay(),
            'useDesc' => $this->getUseDesc(),
            'startTime' => $this->getStartTime(),
            'endTime' => $this->getEndTime(),
            'configuration' => $this->getConfiguration(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'startDateTime' => $this->getStartDateTime()?->format('Y-m-d H:i:s'),
            'endDateTime' => $this->getEndDateTime()?->format('Y-m-d H:i:s'),
            'sn' => $this->getSn(),
            'name' => $this->getName(),
            'type' => $this->getType()->value,
            'expireDay' => $this->getExpireDay(),
            'iconImg' => $this->getIconImg(),
            'remark' => $this->getRemark(),
            'startTime' => $this->getStartTime(),
            'endTime' => $this->getEndTime(),
            'configuration' => $this->getConfiguration(),
        ];
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): void
    {
        if (null === $startTime) {
            $this->startTime = null;
        } elseif ($startTime instanceof \DateTimeImmutable) {
            $this->startTime = $startTime;
        } else {
            $this->startTime = \DateTimeImmutable::createFromInterface($startTime);
        }
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): void
    {
        if (null === $endTime) {
            $this->endTime = null;
        } elseif ($endTime instanceof \DateTimeImmutable) {
            $this->endTime = $endTime;
        } else {
            $this->endTime = \DateTimeImmutable::createFromInterface($endTime);
        }
    }

    public function getResourceId(): string
    {
        return (string) $this->getId();
    }

    public function getResourceLabel(): string
    {
        return (string) $this->getName();
    }

    public function getCommandConfig(): ?CommandConfig
    {
        return $this->commandConfig;
    }

    public function setCommandConfig(?CommandConfig $commandConfig): void
    {
        $this->commandConfig = $commandConfig;
    }

    /**
     * 获取券码数量（用于渲染）
     */
    public function getRenderCodeCount(): int
    {
        return $this->codes->count();
    }
}
