<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Parameters;

use InvalidArgumentException;
use LogicException;

final class TypeReflection
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @param string $type
     * @param string|null $class Class name this parameter type is used in.
     *                           Necessary for relative `self` and `parent` type hints.
     */
    public function __construct(string $type, string $class = null)
    {
        if ($type === 'self' && empty($class)) {
            throw new InvalidArgumentException('Type `self` can only be used inside classes.');
        }
        if ($type === 'parent' && empty($class)) {
            throw new InvalidArgumentException('Type `parent` can only be used inside classes.');
        }

        $this->type = $type;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return PHP_VERSION_ID >= 80000 && $this->type === 'null';
    }

    /**
     * @return bool
     */
    public function isScalar(): bool
    {
        return $this->type === 'bool'
            || $this->type === 'false' && PHP_VERSION_ID >= 80000
            || $this->type === 'int'
            || $this->type === 'float'
            || $this->type === 'string';
    }

    /**
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->type === 'array';
    }

    /**
     * @return bool
     */
    public function isIterable(): bool
    {
        return PHP_VERSION_ID >= 70100 && $this->type === 'iterable';
    }

    /**
     * @return bool
     */
    public function isMixed(): bool
    {
        return PHP_VERSION_ID >= 80000 && $this->type === 'mixed';
    }

    /**
     * @return bool
     */
    public function isObject(): bool
    {
        return PHP_VERSION_ID >= 70200 && $this->type === 'object';
    }

    /**
     * @return bool
     */
    public function isCallable(): bool
    {
        return $this->type === 'callable';
    }

    /**
     * @return bool
     */
    public function isParent(): bool
    {
        return $this->type === 'parent';
    }

    /**
     * @return bool
     */
    public function isSelf(): bool
    {
        return $this->type === 'self';
    }

    /**
     * @return bool
     */
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
     *
     * @param mixed $value
     * @return bool
     */
    public function satisfies($value): bool
    {
        if ($this->isMixed()) {
            return true;
        }

        if ($this->isNull()) {
            return is_null($value);
        }

        if ($this->isClassRequirement()) {
            return is_object($value) && is_a($value, $this->getClassRequirement());
        }

        if ($this->isScalar()) {
            return is_scalar($value) && (
                is_bool($value) && $this->type === 'bool'
                || is_string($value) && $this->type === 'string'
                || is_int($value) && $this->type === 'int'
                || (is_int($value) || is_float($value)) && $this->type === 'float'
                || PHP_VERSION_ID >= 80000 && $value === false && $this->type === 'false'
            );
        }

        return $this->isArray() && is_array($value)
            || $this->isCallable() && is_callable($value)
            || $this->isIterable() && is_iterable($value)
            || $this->isObject() && is_object($value);
    }
}
