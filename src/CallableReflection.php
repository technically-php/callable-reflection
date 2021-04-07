<?php

declare(strict_types=1);

namespace Technically\CallableReflection;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Technically\CallableReflection\Parameters\ParameterReflection;
use Technically\CallableReflection\Parameters\TypeReflection;

final class CallableReflection
{
    private const TYPE_FUNCTION = 1;
    private const TYPE_CLOSURE = 2;
    private const TYPE_INSTANCE_METHOD = 3;
    private const TYPE_STATIC_METHOD = 4;
    private const TYPE_INVOKABLE_OBJECT = 5;
    private const TYPE_CONSTRUCTOR = 6;

    /**
     * @var ReflectionFunction|ReflectionMethod
     */
    private $reflector;

    /**
     * @var int
     */
    private $type;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var ParameterReflection[]
     */
    private $parameters;

    private function __construct(callable $callable, ReflectionFunctionAbstract $reflector, int $type)
    {
        $this->reflector = $reflector;
        $this->callable = $callable;
        $this->parameters = self::reflectParameters($this->reflector);
        $this->type = $type;
    }

    public static function fromCallable(callable $callable): self
    {
        try {
            if ($callable instanceof Closure) {
                return new self($callable, new ReflectionFunction($callable), self::TYPE_CLOSURE);
            }

            if (is_string($callable) && function_exists($callable)) {
                return new self($callable, new ReflectionFunction($callable), self::TYPE_FUNCTION);
            }

            if (is_string($callable) && str_contains($callable, '::')) {
                return new self($callable, new ReflectionMethod($callable), self::TYPE_STATIC_METHOD);
            }

            if (is_object($callable) && method_exists($callable, '__invoke')) {
                return new self($callable, new ReflectionMethod($callable, '__invoke'), self::TYPE_INVOKABLE_OBJECT);
            }

            if (is_array($callable)) {
                $reflector = new ReflectionMethod($callable[0], $callable[1]);

                if ($reflector->isStatic()) {
                    return new self($callable, $reflector, self::TYPE_STATIC_METHOD);
                }

                return new self($callable, $reflector, self::TYPE_INSTANCE_METHOD);
            }
        } catch (ReflectionException $exception) {
            $type = is_object($callable) ? get_class($callable) : gettype($callable);
            throw new RuntimeException("Failed reflecting the given callable: `{$type}`.", 0, $exception);
        }

        $type = is_object($callable) ? get_class($callable) : gettype($callable);
        throw new InvalidArgumentException("Cannot reflect the given callable: `{$type}`.");
    }

    /**
     * @param string $className
     * @return static
     * @throws InvalidArgumentException If the given class does not exist.
     *                                  Or if the class cannot be instantiated.
     */
    public static function fromConstructor(string $className): self
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException("Class `{$className}` does not exist.");
        }

        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $exception) {
            throw new InvalidArgumentException("Class `{$className}` does not exist.", 0, $exception);
        }

        if (! $class->isInstantiable()) {
            throw new InvalidArgumentException("Class `{$className}` cannot be instantiated.");
        }

        if ($reflector = $class->getConstructor()) {
            $constructor = function (...$args) use ($class) {
                return $class->newInstance(...$args);
            };

            return new self($constructor, $reflector, self::TYPE_CONSTRUCTOR);
        }

        $constructor = function () use ($class) {
            return $class->newInstance();
        };

        try {
            $reflector = new ReflectionFunction($constructor);
        } catch (ReflectionException $exception) {
            throw new LogicException('Failed to reflect constructor closure. This should never happen.', 0, $exception);
        }

        return new self($constructor, $reflector, self::TYPE_CONSTRUCTOR);
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    /**
     * @return ParameterReflection[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     */
    public function call(...$arguments)
    {
        return ($this->callable)(...$arguments);
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     */
    public function __invoke(...$arguments)
    {
        return ($this->callable)(...$arguments);
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     * @throws ArgumentCountError If too few or too many arguments passed to the callable.
     */
    public function apply(array $arguments = [])
    {
        $values = $this->resolveArguments($this->getParameters(), $arguments);

        if (count($arguments) > 0) {
            $this->assertUnusedArguments($arguments);
        }

        return ($this->callable)(...$values);
    }

    public function isFunction(): bool
    {
        return $this->type === self::TYPE_FUNCTION;
    }

    public function isConstructor(): bool
    {
        return $this->type === self::TYPE_CONSTRUCTOR;
    }

    public function isClosure(): bool
    {
        return $this->type === self::TYPE_CLOSURE;
    }

    public function isMethod(): bool
    {
        return $this->type === self::TYPE_INSTANCE_METHOD
            || $this->type === self::TYPE_STATIC_METHOD;
    }

    public function isStaticMethod(): bool
    {
        return $this->type === self::TYPE_STATIC_METHOD;
    }

    public function isInstanceMethod(): bool
    {
        return $this->type === self::TYPE_INSTANCE_METHOD;
    }

    public function isInvokableObject(): bool
    {
        return $this->type === self::TYPE_INVOKABLE_OBJECT;
    }

    /**
     * @param ParameterReflection[] $reflections
     * @param array<string|int,mixed> $arguments
     * @return array
     * @throws ArgumentCountError
     */
    private function resolveArguments(array $reflections, array &$arguments): array
    {
        $values = [];
        foreach ($reflections as $i => $reflection) {
            if (array_key_exists($i, $arguments)) {
                $values[] = $arguments[$i];
                unset($arguments[$i]);
                continue;
            }
            if (array_key_exists($reflection->getName(), $arguments)) {
                $values[] = $arguments[$reflection->getName()];
                unset($arguments[$reflection->getName()]);
                continue;
            }
            if ($reflection->isOptional()) {
                $values[] = $reflection->getDefaultValue();
                continue;
            }
            if ($reflection->hasTypes() && $reflection->isNullable()) {
                $values[] = null;
                continue;
            }

            throw new ArgumentCountError(
                "Too few arguments: argument #{$i} (`{$reflection->getName()}`) is expected, but not passed."
            );
        }

        return $values;
    }

    private static function reflectParameters(ReflectionFunctionAbstract $reflector): array
    {
        $parameters = [];

        foreach ($reflector->getParameters() as $parameter) {
            $className = $reflector instanceof ReflectionMethod ? $reflector->getDeclaringClass()->getName() : null;
            $types = self::reflectParameterTypes($parameter, $className);

            $parameters[] = new ParameterReflection(
                $parameter->getName(),
                $types,
                $parameter->isOptional(),
                $parameter->allowsNull(),
                $parameter->isOptional() ? $parameter->getDefaultValue() : null
            );
        }

        return $parameters;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param string|null $className
     * @return TypeReflection[]
     */
    private static function reflectParameterTypes(ReflectionParameter $parameter, ?string $className): array
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            return [
                new TypeReflection($type->getName(), $className),
            ];
        }

        /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
        if (PHP_VERSION_ID >= 80000 && $type instanceof \ReflectionUnionType) {
            return array_map(
                function (ReflectionNamedType $type) use ($className) {
                    return new TypeReflection($type->getName(), $className);
                },
                $type->getTypes()
            );
        }

        return [];
    }

    /**
     * @param array $arguments
     * @throws ArgumentCountError When arguments array is not empty (i.e. there are unused arguments).
     */
    private function assertUnusedArguments(array $arguments): void
    {
        if (count($arguments) === 0) {
            return;
        }

        $keys = array_map(
            function ($key): string {
                if (is_int($key)) {
                    return sprintf("#%s", $key + 1);
                }

                return sprintf("`%s`", $key);
            },
            array_keys($arguments)
        );
        throw new ArgumentCountError(sprintf(
            "Too many arguments: unused extra arguments passed: %s. This may be a mistake in your code.",
            implode(', ', $keys)
        ));
    }
}
