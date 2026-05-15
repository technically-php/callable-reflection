<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\Tests\Fixtures\MyClass;
use Technically\CallableReflection\Tests\Fixtures\MyClassWithConstructor;
use Technically\CallableReflection\Tests\Fixtures\MyClassWithConstructorPropertiesPromotion;
use Technically\CallableReflection\Tests\Fixtures\MyInterface;

describe('CallableReflection::fromConstructor', function () {
    it('should throw exception if the given value is not a valid class name', function () {
        try {
            CallableReflection::fromConstructor('FooBar');
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        expect($exception->getMessage())->toBe('Class `FooBar` does not exist.');
    });

    it('should throw exception if the given class cannot be instantiated', function () {
        try {
            CallableReflection::fromConstructor(Closure::class);
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        expect($exception->getMessage())->toBe('Class `Closure` cannot be instantiated.');
    });

    it('should throw exception for interfaces', function () {
        try {
            CallableReflection::fromConstructor(MyInterface::class);
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        expect($exception->getMessage())->toBe(sprintf('Class `%s` does not exist.', MyInterface::class));
    });

    it('should reflect classes without constructor', function () {
        $reflection = CallableReflection::fromConstructor(MyClass::class);

        expect($reflection)->toBeInstanceOf(CallableReflection::class);
        expect($reflection->isConstructor())->toBeTrue();
        expect($reflection->getParameters())->toBe([]);

        $instance = $reflection->call();

        expect($instance)->toBeInstanceOf(MyClass::class);
    });

    it('should reflect classes with constructor', function () {
        $reflection = CallableReflection::fromConstructor(MyClassWithConstructor::class);

        expect($reflection)->toBeInstanceOf(CallableReflection::class);
        expect($reflection->isConstructor())->toBeTrue();
        expect(count($reflection->getParameters()))->toBe(3);

        [$message, $code, $previous] = $reflection->getParameters();

        expect($message->getName())->toBe('message');
        expect($message->isNullable())->toBeFalse();
        expect($message->isOptional())->toBeFalse();
        expect($message->getTypes())->toEqual([
            new TypeReflection('string', MyClassWithConstructor::class),
        ]);

        expect($code->getName())->toBe('code');
        expect($code->isNullable())->toBeFalse();
        expect($code->isOptional())->toBeTrue();
        expect($code->getDefaultValue())->toBe(0);
        expect($code->getTypes())->toEqual([
            new TypeReflection('int', MyClassWithConstructor::class),
        ]);

        expect($previous->getName())->toBe('previous');
        expect($previous->isNullable())->toBeTrue();
        expect($previous->isOptional())->toBeTrue();
        expect($previous->getDefaultValue())->toBeNull();
        expect($previous->getTypes())->toEqual([
            new TypeReflection(Throwable::class, MyClassWithConstructor::class),
        ]);

        $instance = $reflection->call('Hello', 10, $exception = new Exception());

        expect($instance)->toBeInstanceOf(MyClassWithConstructor::class);
        expect($instance->message)->toBe('Hello');
        expect($instance->code)->toBe(10);
        expect($instance->previous)->toBe($exception);
    });

    it('should reflect constructors with promoted properties', function () {
        $reflection = CallableReflection::fromConstructor(MyClassWithConstructorPropertiesPromotion::class);

        expect($reflection->isConstructor())->toBeTrue();
        expect(count($reflection->getParameters()))->toBe(2);

        [$name, $code] = $reflection->getParameters();

        expect($name->getName())->toBe('name');
        expect($name->isOptional())->toBeFalse();
        expect($name->isPromoted())->toBeTrue();
        expect($name->getTypes())->toEqual([
            new TypeReflection('string', MyClassWithConstructorPropertiesPromotion::class),
        ]);

        expect($code->getName())->toBe('code');
        expect($name->isOptional())->toBeFalse();
        expect($name->isPromoted())->toBeTrue();
        expect($code->getTypes())->toEqual([
            new TypeReflection('int', MyClassWithConstructorPropertiesPromotion::class),
        ]);

        $instance = $reflection->call('Tommy', 7);

        expect($instance)->toBeInstanceOf(MyClassWithConstructorPropertiesPromotion::class);
        expect($instance->name)->toBe('Tommy');
        expect($instance->code)->toBe(7);
    });
});
