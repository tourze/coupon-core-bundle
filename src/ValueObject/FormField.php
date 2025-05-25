<?php

namespace Tourze\CouponCoreBundle\ValueObject;

/**
 * 表单字段值对象
 */
class FormField
{
    private function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly string $label,
        private readonly bool $required = false,
        private readonly ?string $help = null,
        private readonly array $options = [],
        private readonly array $constraints = []
    ) {}

    public static function create(string $name, string $type, string $label): self
    {
        return new self($name, $type, $label);
    }

    public function required(bool $required = true): self
    {
        return new self(
            $this->name,
            $this->type,
            $this->label,
            $required,
            $this->help,
            $this->options,
            $this->constraints
        );
    }

    public function help(string $help): self
    {
        return new self(
            $this->name,
            $this->type,
            $this->label,
            $this->required,
            $help,
            $this->options,
            $this->constraints
        );
    }

    public function options(array $options): self
    {
        return new self(
            $this->name,
            $this->type,
            $this->label,
            $this->required,
            $this->help,
            array_merge($this->options, $options),
            $this->constraints
        );
    }

    public function constraints(array $constraints): self
    {
        return new self(
            $this->name,
            $this->type,
            $this->label,
            $this->required,
            $this->help,
            $this->options,
            array_merge($this->constraints, $constraints)
        );
    }

    public function min(int|float $min): self
    {
        return $this->constraints(['min' => $min]);
    }

    public function max(int|float $max): self
    {
        return $this->constraints(['max' => $max]);
    }

    public function choices(array $choices): self
    {
        return $this->options(['choices' => $choices]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'required' => $this->required,
            'help' => $this->help,
            'options' => $this->options,
            'constraints' => $this->constraints,
        ];
    }
}
