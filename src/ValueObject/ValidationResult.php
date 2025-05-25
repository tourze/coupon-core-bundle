<?php

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 验证结果值对象
 */
class ValidationResult
{
    private function __construct(
        private readonly bool $valid,
        private readonly array $errors = []
    ) {}

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(array $errors): self
    {
        return new self(false, $errors);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}
