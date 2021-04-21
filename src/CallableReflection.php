<?php

declare(strict_types=1);

namespace Technically\CallableReflection;

use ArgumentCountError;
use Closure;
use Error;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use RuntimeException;
use Technically\CallableReflection\Parameters\ParameterReflection;

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

    /**
     * @var array<string,ParameterReflection>
     */
    private $parametersMap;

    /**
     * @var ParameterReflection|null
     */
    private $variadic;

    private function __construct(callable $callable, ReflectionFunctionAbstract $reflector, int $type)
    {
        $this->reflector = $reflector;
        $this->callable = $callable;
        $this->parametersMap = self::reflectParameters($this->reflector);
        $this->parameters = array_values($this->parametersMap);
        $this->variadic = self::findVariadicParameter($this->parameters);
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
            return new self([$class, 'newInstance'], $reflector, self::TYPE_CONSTRUCTOR);
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

    /**
     * @param ParameterReflection[] $parameters
     * @return ParameterReflection|null
     */
    private static function findVariadicParameter(array $parameters): ?ParameterReflection
    {
        $last = $parameters[count($parameters) - 1] ?? null;

        return $last && $last->isVariadic() ? $last : null;
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
        return $this->apply($arguments);
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     */
    public function __invoke(...$arguments)
    {
        return $this->apply($arguments);
    }

    /**
     * @param array $arguments
     * @return mixed
     * @throws ArgumentCountError If too few or too many arguments passed to the callable.
     */
    public function apply(array $arguments)
    {
        $values = $this->resolveArguments($this->getParameters(), $arguments);

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
     * @throws ArgumentCountError When too few or too many arguments passed.
     * @throws Error When positional arguments passed after named arguments.
     * @throws Error When named parameter overwrites previous positional argument value.
     * @throws Error When unknown named parameter passed.
     */
    private function resolveArguments(array $reflections, array $arguments): array
    {
        /**
         * Map holding parameter values by name.
         * @var array<string,mixed>
         */
        $valuesMap = [];

        /**
         * An incremental index of the current parameter being used for positional arguments.
         *
         * This needed is to stay close to PHP8 named/positional arguments unpacking logic.
         * @see https://wiki.php.net/rfc/named_params#variadic_functions_and_argument_unpacking
         */
        $positionalArgumentIndex = 0;

        /**
         * A flag to disallow positional arguments after the first named argument encountered.
         *
         * This needed is to stay close to PHP8 named/positional arguments unpacking logic.
         * @see https://wiki.php.net/rfc/named_params#variadic_functions_and_argument_unpacking
         */
        $allowPositionalArguments = true;

        foreach ($arguments as $i => $argument) {
            if (is_int($i)) {
                if (! $allowPositionalArguments) {
                    throw new Error('Cannot use positional argument after named argument.');
                }
                if (! isset($this->parameters[$positionalArgumentIndex])) {
                    throw new ArgumentCountError("Too many arguments: unexpected extra argument passed: #{$i}.");
                }

                $parameter = $this->parameters[$positionalArgumentIndex];

                if ($parameter->isVariadic()) {
                    $valuesMap[$parameter->getName()][] = $argument;
                    continue;  // Continue without incrementing `$positionalArgumentIndex`.
                }

                $valuesMap[$parameter->getName()] = $argument;
                $positionalArgumentIndex++;

                continue;
            }

            if (is_string($i)) {
                $allowPositionalArguments = false;

                if (array_key_exists($i, $valuesMap)) {
                    throw new Error("Named parameter `{$i}` overwrites positional argument.");
                }

                if (array_key_exists($i, $this->parametersMap)) {
                    $valuesMap[$i] = $argument;
                    continue;
                }

                if ($this->variadic && ! array_key_exists($this->variadic->getName(), $arguments)) {
                    $valuesMap[$this->variadic->getName()][$i] = $argument;
                    continue;
                }

                throw new Error("Unknown named parameter `{$i}`.");
            }
        }

        $values = [];

        foreach ($reflections as $i => $reflection) {
            if ($reflection->isVariadic()) {
                $values = array_merge($values, $valuesMap[$reflection->getName()] ?? []);
                continue;
            }
            if (array_key_exists($reflection->getName(), $valuesMap)) {
                $values[] = $valuesMap[$reflection->getName()];
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
                sprintf("Too few arguments: Argument #%s (`%s`) is not passed.", $i + 1, $reflection->getName())
            );
        }

        return $values;
    }

    /**
     * @param ReflectionFunctionAbstract $reflector
     * @return array<string,ParameterReflection>
     */
    private static function reflectParameters(ReflectionFunctionAbstract $reflector): array
    {
        $parameters = [];

        foreach ($reflector->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = ParameterReflection::fromReflection($parameter);
        }

        return $parameters;
    }
}
