<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Parameters;

use InvalidArgumentException;
use LogicException;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

final readonly class ParameterReflection
{
    private string $name;

    /**
     * @var TypeReflection[]
     */
    private array $types;

    private bool $optional;

    private bool $nullable;

    private bool $variadic;

    private bool $promoted;

    /**
     * @var mixed|null
     */
    private mixed $default;

    /**
     * @internal Please do not instantiate ParameterReflection instances directly.
     *           This API is considered internal and may be modified without changing major version number.
     *
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
        mixed $default = null
    ) {
        $this->name = $name;
        foreach ($types as $type) {
            if (! $type instanceof TypeReflection) {
                throw new InvalidArgumentException('Type must be an instance of TypeReflection');
            }
        }
        $this->types = array_values($types);
        $this->optional = $optional;
        $this->nullable = $nullable;
        $this->variadic = $variadic;
        $this->promoted = $promoted;
        $this->default = $default;
    }

    public static function fromReflection(ReflectionParameter $reflection): self
    {
        $types = [];

        $className = (! $reflection->getDeclaringFunction()->isClosure() && $reflection->getDeclaringClass())
            ? $reflection->getDeclaringClass()->getName()
            : null;

        if ($type = $reflection->getType()) {
            if ($type instanceof ReflectionNamedType) {
                $types = [
                    new TypeReflection($type->getName(), $className),
                ];
            }

            if ($type instanceof ReflectionUnionType) {
                $types = array_map(
                    fn (ReflectionNamedType $type) => new TypeReflection($type->getName(), $className),
                    $type->getTypes(),
                );
            }
        }

        return new self(
            $reflection->getName(),
            $types,
            $reflection->isOptional(),
            $reflection->allowsNull(),
            $reflection->isVariadic(),
            $reflection->isPromoted(),
            self::analyzeDefaultValue($reflection),
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

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

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    public function isPromoted(): bool
    {
        return $this->promoted;
    }

    public function getDefaultValue(): mixed
    {
        return $this->default;
    }

    /**
     * Check if the given value satisfies the parameter type declarations.
     */
    public function satisfies(mixed $value): bool
    {
        if ($this->isVariadic()) {
            return $this->satisfiesVariadic($value);
        }

        return $this->satisfiesSingular($value);
    }

    private static function analyzeDefaultValue(ReflectionParameter $reflection): mixed
    {
        if ($reflection->isVariadic()) {
            return [];
        }

        if (! $reflection->isDefaultValueAvailable()) {
            return null;
        }

        try {
            return $reflection->getDefaultValue();
        } catch (ReflectionException $exception) {
            throw new LogicException(
                'Failed to get parameter default value. This should never happen.',
                0,
                $exception
            );
        }
    }

    private function satisfiesVariadic(mixed $value): bool
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

    private function satisfiesSingular(mixed $value): bool
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
