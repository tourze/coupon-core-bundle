<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\CouponCoreBundle\Repository\CouponStatRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: CouponStatRepository::class)]
#[ORM\Table(name: 'coupon_stat', options: ['comment' => '优惠券统计'])]
class CouponStat
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '优惠券id'])]
    private string $couponId;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '总数量', 'default' => 0])]
    private int $totalNum = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '已领取数量', 'default' => 0])]
    private int $receivedNum = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '已使用数量', 'default' => 0])]
    private int $usedNum = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '已过期数量', 'default' => 0])]
    private int $expiredNum = 0;

    #[IndexColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]#[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]public function getId(): ?string
    {
        return $this->id;
    }

    public function getCouponId(): string
    {
        return $this->couponId;
    }

    public function setCouponId(string $couponId): void
    {
        $this->couponId = $couponId;
    }

    public function getTotalNum(): int
    {
        return $this->totalNum;
    }

    public function setTotalNum(int $totalNum): void
    {
        $this->totalNum = $totalNum;
    }

    public function getReceivedNum(): int
    {
        return $this->receivedNum;
    }

    public function setReceivedNum(int $receivedNum): void
    {
        $this->receivedNum = $receivedNum;
    }

    public function getUsedNum(): int
    {
        return $this->usedNum;
    }

    public function setUsedNum(int $usedNum): void
    {
        $this->usedNum = $usedNum;
    }

    public function getExpiredNum(): int
    {
        return $this->expiredNum;
    }

    public function setExpiredNum(int $expiredNum): void
    {
        $this->expiredNum = $expiredNum;
    }}
