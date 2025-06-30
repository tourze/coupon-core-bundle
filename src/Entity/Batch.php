<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\CouponCoreBundle\Repository\BatchRepository;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: BatchRepository::class)]
#[ORM\Table(name: 'coupon_batch', options: ['comment' => '批次'])]
class Batch implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(inversedBy: 'batches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Coupon $coupon = null;

    #[ORM\Column(options: ['comment' => '总数量'])]
    private ?int $totalNum = null;

    #[ORM\Column(options: ['comment' => '已发送数量'])]
    private ?int $sendNum = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注', 'default' => ''])]
    private ?string $remark = null;


    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): self
    {
        $this->coupon = $coupon;

        return $this;
    }

    public function getTotalNum(): ?int
    {
        return $this->totalNum;
    }

    public function setTotalNum(int $totalNum): self
    {
        $this->totalNum = $totalNum;

        return $this;
    }

    public function getSendNum(): ?int
    {
        return $this->sendNum;
    }

    public function setSendNum(int $sendNum): self
    {
        $this->sendNum = $sendNum;

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

    public function __toString(): string
    {
        return sprintf('Batch #%s (%d/%d)', $this->id ?? '', $this->sendNum ?? 0, $this->totalNum ?? 0);
    }
}
