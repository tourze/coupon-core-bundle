<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\CouponCoreBundle\Repository\ChannelRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: ChannelRepository::class)]
#[ORM\Table(name: 'coupon_channel', options: ['comment' => '渠道'])]
class Channel implements \Stringable, PlainArrayInterface, ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[ORM\Column(length: 60, options: ['comment' => '标题'])]
    private ?string $title = null;

    #[RandomStringColumn(length: 10)]
    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, nullable: true, options: ['comment' => '编码'])]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '描述'])]
    private ?string $remark = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => 'logo'])]
    private ?string $logo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '跳转链接'])]
    private ?string $redirectUrl = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '小程序AppID'])]
    private ?string $appId = '';

    /**
     * @var Collection<Code>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Code::class, mappedBy: 'channel', orphanRemoval: true)]
    private Collection $codes;

    #[Ignore]
    #[ORM\ManyToMany(targetEntity: Coupon::class, mappedBy: 'channels', fetch: 'EXTRA_LAZY')]
    private Collection $coupons;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;


    public function __construct()
    {
        $this->codes = new ArrayCollection();
        $this->coupons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
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
            $code->setChannel($this);
        }

        return $this;
    }

    public function removeCode(Code $code): self
    {
        if ($this->codes->removeElement($code)) {
            // set the owning side to null (unless already changed)
            if ($code->getChannel() === $this) {
                $code->setChannel(null);
            }
        }

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    public function addCoupon(Coupon $coupon): self
    {
        if (!$this->coupons->contains($coupon)) {
            $this->coupons->add($coupon);
        }

        return $this;
    }

    public function removeCoupon(Coupon $coupon): self
    {
        $this->coupons->removeElement($coupon);

        return $this;
    }

    public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'code' => $this->getCode(),
            'title' => $this->getTitle(),
            'remark' => $this->getRemark(),
            'logo' => $this->getLogo(),
            'redirectUrl' => $this->getRedirectUrl(),
            'appId' => $this->getAppId(),
        ];
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): void
    {
        $this->appId = $appId;
    }

    public function retrieveApiArray(): array
    {
        return $this->retrievePlainArray();
    }

    public function retrieveAdminArray(): array
    {
        return $this->retrievePlainArray();
    }

}
