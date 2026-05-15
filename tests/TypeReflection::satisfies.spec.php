<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\Tests\Fixtures\MyAnotherEnum;
use Technically\CallableReflection\Tests\Fixtures\MyClass;
use Technically\CallableReflection\Tests\Fixtures\MyEnum;
use Technically\CallableReflection\Tests\Fixtures\MyExtendedClass;
use Technically\CallableReflection\Tests\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Tests\Fixtures\MyInterface;

describe('TypeReflection::satisfies()', function () {
    $closure = function () {
        yield 1;
    };
    $iterator = $closure();

    it('should check if a value satisfies `void` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('void');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `mixed` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('mixed');

        expect($type->satisfies(true))->toBeTrue();
        expect($type->satisfies(false))->toBeTrue();
        expect($type->satisfies(null))->toBeTrue();
        expect($type->satisfies(''))->toBeTrue();
        expect($type->satisfies('is_object'))->toBeTrue();
        expect($type->satisfies([]))->toBeTrue();
        expect($type->satisfies($closure))->toBeTrue();
        expect($type->satisfies($iterator))->toBeTrue();
        expect($type->satisfies(1))->toBeTrue();
        expect($type->satisfies(1.0))->toBeTrue();
        expect($type->satisfies(2.5))->toBeTrue();
        expect($type->satisfies((object) []))->toBeTrue();
    });

    it('should check if a value satisfies `null` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('null');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeTrue();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `false` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('false');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeTrue();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `bool` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('bool');

        expect($type->satisfies(true))->toBeTrue();
        expect($type->satisfies(false))->toBeTrue();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `string` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('string');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeTrue();
        expect($type->satisfies('is_object'))->toBeTrue();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `int` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('int');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeTrue();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `float` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('float');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeTrue(); // int values satisfy `float` type as well
        expect($type->satisfies(1.0))->toBeTrue();
        expect($type->satisfies(2.5))->toBeTrue();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `object` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('object');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeTrue();
        expect($type->satisfies($iterator))->toBeTrue();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeTrue();
    });

    it('should check if a value satisfies `iterable` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('iterable');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeTrue();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeTrue();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `array` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('array');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies([]))->toBeTrue();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies `callable` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('callable');

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeTrue();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeTrue();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
    });

    it('should check if a value satisfies an interface type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection(MyInterface::class);

        $instance = new MyClass();
        $another = new MyInstanceMethodCallable();

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies(MyInterface::class))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
        expect($type->satisfies($instance))->toBeTrue();
        expect($type->satisfies($another))->toBeFalse();
    });

    it('should check if a value satisfies class type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection(MyClass::class);

        $instance = new MyClass();
        $another = new MyInstanceMethodCallable();

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies(MyInterface::class))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
        expect($type->satisfies($instance))->toBeTrue();
        expect($type->satisfies($another))->toBeFalse();
    });

    it('should check if a value satisfies interface type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection(Stringable::class);

        $instance = new class implements Stringable {
            public function __toString()
            {
                return 'MyLocalClass';
            }
        };

        $another = new class {};

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies(MyInterface::class))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
        expect($type->satisfies($instance))->toBeTrue();
        expect($type->satisfies($another))->toBeFalse();
    });

    it('should check if a value satisfies enum type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection(MyEnum::class);

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies(MyInterface::class))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
        expect($type->satisfies(MyEnum::UNO))->toBeTrue();
        expect($type->satisfies(MyAnotherEnum::ONE))->toBeFalse();
    });

    it('should check if a value satisfies `self` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('self', MyClass::class);

        $instance = new MyClass();
        $another = new MyInstanceMethodCallable();

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies(MyInterface::class))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
        expect($type->satisfies($instance))->toBeTrue();
        expect($type->satisfies($another))->toBeFalse();
    });

    it('should check if a value satisfies `parent` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('parent', MyExtendedClass::class);

        $instance = new MyExtendedClass();
        $parent = new MyClass();
        $another = new MyInstanceMethodCallable();

        expect($type->satisfies(true))->toBeFalse();
        expect($type->satisfies(false))->toBeFalse();
        expect($type->satisfies(null))->toBeFalse();
        expect($type->satisfies(''))->toBeFalse();
        expect($type->satisfies('is_object'))->toBeFalse();
        expect($type->satisfies(MyInterface::class))->toBeFalse();
        expect($type->satisfies([]))->toBeFalse();
        expect($type->satisfies($closure))->toBeFalse();
        expect($type->satisfies($iterator))->toBeFalse();
        expect($type->satisfies(1))->toBeFalse();
        expect($type->satisfies(1.0))->toBeFalse();
        expect($type->satisfies(2.5))->toBeFalse();
        expect($type->satisfies((object) []))->toBeFalse();
        expect($type->satisfies($instance))->toBeTrue();
        expect($type->satisfies($parent))->toBeTrue();
        expect($type->satisfies($another))->toBeFalse();
    });
});
