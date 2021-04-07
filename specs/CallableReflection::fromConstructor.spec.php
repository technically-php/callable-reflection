<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\Specs\Fixtures\MyClass;
use Technically\CallableReflection\Specs\Fixtures\MyClassWithConstructor;
use Technically\CallableReflection\Specs\Fixtures\MyInterface;

describe('CallableReflection::fromConstructor', function () {
    it('should throw exception if the given value is not a valid class name', function () {
        try {
            CallableReflection::fromConstructor('FooBar');
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === 'Class `FooBar` does not exist.');
    });

    it('should throw exception if the given class cannot be instantiated', function () {
        try {
            CallableReflection::fromConstructor(Closure::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === 'Class `Closure` cannot be instantiated.');
    });

    it('should throw exception for interfaces', function () {
        try {
            CallableReflection::fromConstructor(MyInterface::class);
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === sprintf('Class `%s` does not exist.', MyInterface::class));
    });

    it('should reflect classes without constructor', function () {
        $reflection = CallableReflection::fromConstructor(MyClass::class);

        assert($reflection instanceof CallableReflection);
        assert($reflection->isConstructor() === true);
        assert($reflection->getParameters() === []);

        $instance = $reflection->call();

        assert($instance instanceof MyClass);
    });

    it('should reflect classes with constructor', function () {
        $reflection = CallableReflection::fromConstructor(MyClassWithConstructor::class);

        assert($reflection instanceof CallableReflection);
        assert($reflection->isConstructor() === true);
        assert(count($reflection->getParameters()) === 3);

        [$message, $code, $previous] = $reflection->getParameters();

        assert($message->getName() === 'message');
        assert($message->isNullable() === false);
        assert($message->isOptional() === false);
        assert($message->getTypes() == [
            new TypeReflection('string', MyClassWithConstructor::class),
        ]);

        assert($code->getName() === 'code');
        assert($code->isNullable() === false);
        assert($code->isOptional() === true);
        assert($code->getDefaultValue() === 0);
        assert($code->getTypes() == [
            new TypeReflection('int', MyClassWithConstructor::class),
        ]);

        assert($previous->getName() === 'previous');
        assert($previous->isNullable() === true);
        assert($previous->isOptional() === true);
        assert($previous->getDefaultValue() === null);
        assert($previous->getTypes() == [
            new TypeReflection(Throwable::class, MyClassWithConstructor::class),
        ]);

        $instance = $reflection->call('Hello', 10, $exception = new Exception());

        assert($instance instanceof MyClassWithConstructor);
        assert($instance->message === 'Hello');
        assert($instance->code === 10);
        assert($instance->previous === $exception);
    });
});
