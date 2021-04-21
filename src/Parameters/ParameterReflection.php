<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Parameters;

use LogicException;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

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
     * @param ReflectionParameter $reflection
     * @return static
     */
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

            /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
            if (PHP_VERSION_ID >= 80000 && $type instanceof \ReflectionUnionType) {
                $types = array_map(
                    function (ReflectionNamedType $type) use ($className) {
                        return new TypeReflection($type->getName(), $className);
                    },
                    $type->getTypes()
                );
            }
        }

        /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
        return new self(
            $reflection->getName(),
            $types,
            $reflection->isOptional(),
            $reflection->allowsNull(),
            $reflection->isVariadic(),
            PHP_VERSION_ID >= 80000 && $reflection->isPromoted(),
            self::analyzeDefaultValue($reflection)
        );
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

    /**
     * @param ReflectionParameter $reflection
     * @return mixed
     */
    private static function analyzeDefaultValue(ReflectionParameter $reflection)
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
            throw new LogicException('Failed to get parameter default value. This should never happen.',
                $exception);
        }
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
