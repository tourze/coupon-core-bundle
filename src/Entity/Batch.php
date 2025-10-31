<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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

    #[ORM\ManyToOne(inversedBy: 'batches', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Coupon $coupon = null;

    #[ORM\Column(options: ['comment' => '总数量'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $totalNum = null;

    #[ORM\Column(options: ['comment' => '已发送数量'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $sendNum = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注', 'default' => ''])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): void
    {
        $this->coupon = $coupon;
    }

    public function getTotalNum(): ?int
    {
        return $this->totalNum;
    }

    public function setTotalNum(int $totalNum): void
    {
        $this->totalNum = $totalNum;
    }

    public function getSendNum(): ?int
    {
        return $this->sendNum;
    }

    public function setSendNum(int $sendNum): void
    {
        $this->sendNum = $sendNum;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function __toString(): string
    {
        return sprintf('Batch #%s (%d/%d)', $this->id ?? '', $this->sendNum ?? 0, $this->totalNum ?? 0);
    }
}
