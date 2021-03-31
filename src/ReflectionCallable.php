<?php

namespace Technically\ReflectionCallable;

use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Reflector;
use RuntimeException;

final class ReflectionCallable
{
    /**
     * @var ReflectionFunction|ReflectionMethod
     */
    private $reflector;

    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->reflector = self::reflect($callable);
        $this->callable = $callable;
    }

    /**
     * @return ReflectionMethod|ReflectionFunction
     */
    public function getReflector(): Reflector
    {
        return $this->reflector;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     */
    public function call(...$arguments)
    {
        return ($this->callable)(...$arguments);
    }

    public function isFunction(): bool
    {
        return $this->reflector instanceof ReflectionFunction
            && is_string($this->callable)
            && function_exists($this->callable);
    }

    public function isClosure(): bool
    {
        return $this->callable instanceof Closure;
    }

    public function isMethod(): bool
    {
        return ! is_object($this->callable)
            && $this->reflector instanceof ReflectionMethod;
    }

    public function isStaticMethod(): bool
    {
        return $this->reflector instanceof ReflectionMethod
            && $this->reflector->isStatic();
    }

    public function isInstanceMethod(): bool
    {
        return $this->reflector instanceof ReflectionMethod
            && is_array($this->callable)
            && is_object($this->callable[0]);
    }

    public function isInvokableObject(): bool
    {
        if ($this->isClosure()) {
            return false;
        }

        return is_object($this->callable);
    }

    /**
     * @param callable $callable
     * @return ReflectionFunction|ReflectionMethod
     */
    private static function reflect(callable $callable): Reflector
    {
        try {
            if ($callable instanceof Closure) {
                return new ReflectionFunction($callable);
            }

            if (is_string($callable) && function_exists($callable)) {
                return new ReflectionFunction($callable);
            }

            if (is_string($callable) && str_contains($callable, '::')) {
                return new ReflectionMethod($callable);
            }

            if (is_object($callable) && method_exists($callable, '__invoke')) {
                return new ReflectionMethod($callable, '__invoke');
            }

            if (is_array($callable)) {
                return new ReflectionMethod($callable[0], $callable[1]);
            }
        } catch (ReflectionException $exception) {
            throw new RuntimeException(
                sprintf('Failed reflecting the given callable: %s.', get_debug_type($callable)),
                0,
                $exception
            );
        }

        throw new InvalidArgumentException(
            sprintf("Cannot reflect the given callable: %s.", get_debug_type($callable))
        );
    }
}
