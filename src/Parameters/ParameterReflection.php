<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Parameters;

final class ParameterReflection
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TypeReflection[]
     */
    private $types;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var bool
     */
    private $variadic;

    /**
     * @var bool
     */
    private $promoted;

    /**
     * @var mixed|null
     */
    private $default;

    /**
     * @param string $name
     * @param TypeReflection[] $types
     * @param bool $optional
     * @param bool $nullable
     * @param bool $variadic
     * @param bool $promoted
     * @param mixed|null $default
     */
    public function __construct(
        string $name,
        array $types,
        bool $optional = false,
        bool $nullable = false,
        bool $variadic = false,
        bool $promoted = false,
        $default = null
    ) {
        $this->name = $name;
        $this->types = (function (TypeReflection ...$types): array {
            return $types;
        })(...$types);
        $this->optional = $optional;
        $this->nullable = $nullable;
        $this->variadic = $variadic;
        $this->promoted = $promoted;
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasTypes(): bool
    {
        return count($this->types) > 0;
    }

    /**
     * @return TypeReflection[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return bool
     */
    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    /**
     * @return bool
     */
    public function isPromoted(): bool
    {
        return $this->promoted;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    /**
     * Check if the given value satisfies the parameter type declarations.
     *
     * @param mixed $value
     * @return bool
     */
    public function satisfies($value): bool
    {
        if ($this->isVariadic()) {
            return $this->satisfiesVariadic($value);
        }

        return $this->satisfiesSingular($value);
    }

    private function satisfiesVariadic($value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (empty($this->types)) {
            // Parameters without type declarations allow everything (like `mixed`).
            return true;
        }

        foreach ($value as $item) {
            if (! $this->satisfiesSingular($item)) {
                return false;
            }
        }

        return true;
    }

    private function satisfiesSingular($value): bool
    {
        if (empty($this->types)) {
            // Parameters without type declarations allow everything (like `mixed`).
            return true;
        }

        if (is_null($value) && $this->isNullable()) {
            return true;
        }

        foreach ($this->types as $type) {
            if ($type->satisfies($value)) {
                return true;
            }
        }

        return false;
    }
}
