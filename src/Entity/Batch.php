<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\CouponCoreBundle\Repository\BatchRepository;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;

#[ORM\Entity(repositoryClass: BatchRepository::class)]
#[ORM\Table(name: 'coupon_batch', options: ['comment' => '批次'])]
class Batch
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne(inversedBy: 'batches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Coupon $coupon = null;

    #[ORM\Column]
    private ?int $totalNum = null;

    #[ORM\Column]
    private ?int $sendNum = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注', 'default' => ''])]
    private ?string $remark = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    public function getId(): ?string
    {
        return $this->id;
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
    }}
