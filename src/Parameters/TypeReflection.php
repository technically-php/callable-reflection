<?php

namespace Technically\ReflectionCallable\Parameters;

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
        if ($type === 'null') {
            throw new InvalidArgumentException('Type `null` is intentionally unsupported by this implementation.');
        }
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
        return ! $this->isScalar()
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

        return new LogicException('Cannot get class name for a non-class requirement.');
    }
}
