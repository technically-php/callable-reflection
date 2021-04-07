<?php

namespace Technically\CallableReflection;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
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
    /**
     * @var ReflectionFunction|ReflectionMethod
     */
    private $reflector;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var ParameterReflection[]
     */
    private $parameters;

    public function __construct(callable $callable)
    {
        $this->reflector = self::reflect($callable);
        $this->callable = $callable;
        $this->parameters = self::reflectParameters($this->reflector);
    }

    /**
     * @return ReflectionMethod|ReflectionFunction
     */
    public function getReflector(): ReflectionFunctionAbstract
    {
        return $this->reflector;
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
     * @param ParameterReflection[] $reflections
     * @param array<string|int,mixed> $arguments
     * @return array
     * @throws ArgumentCountError
     */
    private function resolveArguments(array $reflections, array & $arguments): array
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

    /**
     * @param callable $callable
     * @return ReflectionFunction|ReflectionMethod
     */
    private static function reflect(callable $callable): ReflectionFunctionAbstract
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
        if (PHP_VERSION_ID >= 8000 && $type instanceof \ReflectionUnionType) {
            return array_values(array_filter(array_map(
                function (ReflectionNamedType $type) use ($className) {
                    if ($type->getName() === 'null') {
                        return null;
                    }

                    return new TypeReflection($type->getName(), $className);
                },
                $type->getTypes()
            )));
        }

        return [];
    }

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
