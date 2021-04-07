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
     * @var mixed|null
     */
    private $default;

    /**
     * @param string $name
     * @param TypeReflection[] $types
     * @param bool $optional
     * @param bool $nullable
     * @param mixed|null $default
     */
    public function __construct(
        string $name,
        array $types,
        bool $optional = false,
        bool $nullable = false,
        $default = null
    ) {
        $this->name = $name;
        $this->types = (function (TypeReflection ...$types): array {
            return $types;
        })(...$types);
        $this->optional = $optional;
        $this->nullable = $nullable;
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
