<?php

namespace Tourze\CouponCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCoreBundle\Repository\ReadStatusRepository;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: ReadStatusRepository::class)]
#[ORM\Table(name: 'coupon_code_read_status', options: ['comment' => '码被查看情况'])]
class ReadStatus implements ApiArrayInterface, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[Ignore]
    #[ORM\OneToOne(inversedBy: 'readStatus', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Code $code = null;


    public function getId(): ?string
    {
        return $this->id;
    }


    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'createdBy' => $this->getCreatedBy(),
            'updatedBy' => $this->getUpdatedBy(),
        ];
    }

    public function getCode(): ?Code
    {
        return $this->code;
    }

    public function setCode(Code $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('ReadStatus #%s for Code %s', $this->id ?? '', $this->code?->getSn() ?? '');
    }
}
