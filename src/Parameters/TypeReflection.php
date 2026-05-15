<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Parameters;

use InvalidArgumentException;
use LogicException;

final readonly class TypeReflection
{
    private const ARRAY = 'array';
    private const BOOL = 'bool';
    private const CALLABLE = 'callable';
    private const FALSE = 'false';
    private const FLOAT = 'float';
    private const INT = 'int';
    private const ITERABLE = 'iterable';
    private const MIXED = 'mixed';
    private const NULL = 'null';
    private const OBJECT = 'object';
    private const PARENT = 'parent';
    private const SELF = 'self';
    private const STRING = 'string';
    private const TRUE = 'true';
    private const VOID = 'void';

    private string $type;

    /**
     * @var class-string|null
     */
    private string | null $class;

    /**
     * @internal Please do not instantiate TypeReflection instances directly.
     *           This API is considered internal and may be modified without changing major version number.
     *
     * @param string            $type
     * @param class-string|null $class Class name this parameter type is used in.
     *                                 Necessary for relative `self` and `parent` type hints.
     */
    public function __construct(string $type, ?string $class = null)
    {
        if ($type === self::SELF && empty($class)) {
            throw new InvalidArgumentException('Type `self` can only be used inside classes.');
        }
        if ($type === self::PARENT && empty($class)) {
            throw new InvalidArgumentException('Type `parent` can only be used inside classes.');
        }

        $this->type = $type;
        $this->class = $class;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isVoid(): bool
    {
        return $this->type === self::VOID;
    }

    public function isNull(): bool
    {
        return $this->type === self::NULL;
    }

    public function isScalar(): bool
    {
        return $this->type === self::BOOL
            || $this->type === self::FALSE
            || $this->type === self::TRUE
            || $this->type === self::INT
            || $this->type === self::FLOAT
            || $this->type === self::STRING;
    }

    public function isArray(): bool
    {
        return $this->type === self::ARRAY;
    }

    public function isIterable(): bool
    {
        return $this->type === self::ITERABLE;
    }

    public function isMixed(): bool
    {
        return $this->type === self::MIXED;
    }

    public function isObject(): bool
    {
        return $this->type === self::OBJECT;
    }

    public function isCallable(): bool
    {
        return $this->type === self::CALLABLE;
    }

    public function isParent(): bool
    {
        return $this->type === self::PARENT;
    }

    public function isSelf(): bool
    {
        return $this->type === self::SELF;
    }

    public function isClassName(): bool
    {
        return ! $this->isNull()
            && ! $this->isScalar()
            && ! $this->isArray()
            && ! $this->isIterable()
            && ! $this->isObject()
            && ! $this->isMixed()
            && ! $this->isCallable()
            && ! $this->isParent()
            && ! $this->isSelf();
    }

    /**
     * @return bool
     */
    public function isClassRequirement(): bool
    {
        return $this->isParent()
            || $this->isSelf()
            || $this->isClassName();
    }

    /**
     * Get class name this type hint is referring to.
     *
     * This will also resolve `self` and `parent` hints that can be used inside classes.
     * @see https://www.php.net/manual/en/language.types.declarations.php
     *
     * @return string
     */
    public function getClassRequirement(): string
    {
        if ($this->isSelf()) {
            return $this->class;
        }

        if ($this->isParent()) {
            return get_parent_class($this->class);
        }

        if ($this->isClassName()) {
            return $this->type;
        }

        throw new LogicException('Cannot get class name for a non-class requirement.');
    }

    /**
     * Check if the given value satisfies the type.
     */
    public function satisfies(mixed $value): bool
    {
        return match ($this->type) {
            self::VOID     => false,
            self::MIXED    => true,
            self::NULL     => is_null($value),
            self::BOOL     => is_bool($value),
            self::STRING   => is_string($value),
            self::INT      => is_int($value),
            self::FLOAT    => is_int($value) || is_float($value),
            self::FALSE    => $value === false,
            self::TRUE     => $value === true,
            self::ARRAY    => is_array($value),
            self::CALLABLE => is_callable($value),
            self::ITERABLE => is_iterable($value),
            self::OBJECT   => is_object($value),
            default        => is_object($value) && is_a($value, $this->getClassRequirement()), // FIXME: checks for interfaces
        };
    }
}
