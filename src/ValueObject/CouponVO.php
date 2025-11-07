<?php

declare(strict_types=1);

namespace Tourze\CouponCoreBundle\ValueObject;

use Tourze\CouponCoreBundle\Enum\CouponType;

/**
 * 优惠券基础价值对象
 *
 * @psalm-type CouponBaseArray = array{
 *     code: string,
 *     type?: string,
 *     name?: string|null,
 *     valid_from?: string|null,
 *     valid_to?: string|null,
 *     scope?: array<string, mixed>,
 *     condition?: array<string, mixed>,
 *     benefit?: array<string, mixed>,
 *     metadata?: array<string, mixed>
 * }
 */
abstract class CouponVO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private readonly string $code,
        private readonly CouponType $type,
        private readonly ?string $name,
        private readonly ?\DateTimeImmutable $validFrom,
        private readonly ?\DateTimeImmutable $validTo,
        private readonly CouponScopeVO $scope,
        private readonly CouponConditionVO $condition,
        private readonly CouponBenefitVO $benefit,
        private readonly array $metadata = [],
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): CouponType
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?\DateTimeImmutable
    {
        return $this->validTo;
    }

    public function getScope(): CouponScopeVO
    {
        return $this->scope;
    }

    public function getCondition(): CouponConditionVO
    {
        return $this->condition;
    }

    public function getBenefit(): CouponBenefitVO
    {
        return $this->benefit;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isWithinValidity(\DateTimeInterface $now): bool
    {
        if (null !== $this->validFrom && $now < $this->validFrom) {
            return false;
        }

        if (null !== $this->validTo && $now > $this->validTo) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type->value,
            'name' => $this->name,
            'valid_from' => $this->validFrom?->format(\DateTimeInterface::ATOM),
            'valid_to' => $this->validTo?->format(\DateTimeInterface::ATOM),
            'scope' => $this->scope->toArray(),
            'condition' => $this->condition->toArray(),
            'benefit' => $this->benefit->toArray(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @param CouponBaseArray $data
     * @return array{
     *     code: string,
     *     type: CouponType,
     *     name: ?string,
     *     validFrom: ?\DateTimeImmutable,
     *     validTo: ?\DateTimeImmutable,
     *     scope: CouponScopeVO,
     *     condition: CouponConditionVO,
     *     benefit: CouponBenefitVO,
     *     metadata: array<string, mixed>
     * }
     */
    protected static function resolveBaseArguments(array $data): array
    {
        $type = self::resolveType($data['type'] ?? null);

        return [
            'code' => isset($data['code']) ? (string) $data['code'] : '',
            'type' => $type,
            'name' => isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            'validFrom' => self::resolveDateTime($data['valid_from'] ?? null),
            'validTo' => self::resolveDateTime($data['valid_to'] ?? null),
            'scope' => CouponScopeVO::fromArray(is_array($data['scope'] ?? null) ? $data['scope'] : []),
            'condition' => CouponConditionVO::fromArray(is_array($data['condition'] ?? null) ? $data['condition'] : []),
            'benefit' => CouponBenefitVO::fromArray(is_array($data['benefit'] ?? null) ? $data['benefit'] : []),
            'metadata' => self::normalizeMetadata($data['metadata'] ?? []),
        ];
    }

    private static function resolveType(mixed $type): CouponType
    {
        $typeValue = $type ?? CouponType::FULL_REDUCTION->value;
        if (!is_string($typeValue) && !is_int($typeValue)) {
            $typeValue = CouponType::FULL_REDUCTION->value;
        }

        $resolved = CouponType::tryFrom((string) $typeValue);
        if (null === $resolved) {
            $displayType = is_scalar($type) ? (string) $type : gettype($type);
            throw new \InvalidArgumentException('Unsupported coupon type: ' . $displayType);
        }

        return $resolved;
    }

    private static function resolveDateTime(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }

    /**
     * @param mixed $metadata
     * @return array<string, mixed>
     */
    private static function normalizeMetadata(mixed $metadata): array
    {
        if (!is_array($metadata)) {
            return [];
        }

        // 确保所有键都是字符串
        $normalized = [];
        foreach ($metadata as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }
}
