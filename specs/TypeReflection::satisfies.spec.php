<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\Specs\Fixtures\MyClass;
use Technically\CallableReflection\Specs\Fixtures\MyExtendedClass;
use Technically\CallableReflection\Specs\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Specs\Fixtures\MyInterface;

describe('TypeReflection::satisfies()', function () {
    $closure = function () {
        yield 1;
    };
    $iterator = $closure();

    if (PHP_VERSION_ID >= 80000) {
        it('should check if a value satisfies `mixed` type declaration', function () use ($closure, $iterator) {
            $type = new TypeReflection('mixed');

            assert($type->satisfies(true) === true);
            assert($type->satisfies(false) === true);
            assert($type->satisfies(null) === true);
            assert($type->satisfies('') === true);
            assert($type->satisfies('is_object') === true);
            assert($type->satisfies([]) === true);
            assert($type->satisfies($closure) === true);
            assert($type->satisfies($iterator) === true);
            assert($type->satisfies(1) === true);
            assert($type->satisfies(1.0) === true);
            assert($type->satisfies(2.5) === true);
            assert($type->satisfies((object) []) === true);
        });

        it('should check if a value satisfies `null` type declaration', function () use ($closure, $iterator) {
            $type = new TypeReflection('null');

            assert($type->satisfies(true) === false);
            assert($type->satisfies(false) === false);
            assert($type->satisfies(null) === true);
            assert($type->satisfies('') === false);
            assert($type->satisfies('is_object') === false);
            assert($type->satisfies([]) === false);
            assert($type->satisfies($closure) === false);
            assert($type->satisfies($iterator) === false);
            assert($type->satisfies(1) === false);
            assert($type->satisfies(1.0) === false);
            assert($type->satisfies(2.5) === false);
            assert($type->satisfies((object) []) === false);
        });

        it('should check if a value satisfies `false` type declaration', function () use ($closure, $iterator) {
            $type = new TypeReflection('false');

            assert($type->satisfies(true) === false);
            assert($type->satisfies(false) === true);
            assert($type->satisfies(null) === false);
            assert($type->satisfies('') === false);
            assert($type->satisfies('is_object') === false);
            assert($type->satisfies([]) === false);
            assert($type->satisfies($closure) === false);
            assert($type->satisfies($iterator) === false);
            assert($type->satisfies(1) === false);
            assert($type->satisfies(1.0) === false);
            assert($type->satisfies(2.5) === false);
            assert($type->satisfies((object) []) === false);
        });
    }

    it('should check if a value satisfies `bool` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('bool');

        assert($type->satisfies(true) === true);
        assert($type->satisfies(false) === true);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies `string` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('string');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === true);
        assert($type->satisfies('is_object') === true);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies `int` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('int');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === true);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies `float` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('float');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === true); // int values satisfy `float` type as well
        assert($type->satisfies(1.0) === true);
        assert($type->satisfies(2.5) === true);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies `object` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('object');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === true);
        assert($type->satisfies($iterator) === true);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === true);
    });

    it('should check if a value satisfies `iterable` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('iterable');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies([]) === true);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === true);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies `array` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('array');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies([]) === true);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies `callable` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('callable');

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === true);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === true);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
    });

    it('should check if a value satisfies an interface type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection(MyInterface::class);

        $instance = new MyClass();
        $another = new MyInstanceMethodCallable();

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies(MyInterface::class) === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
        assert($type->satisfies($instance) === true);
        assert($type->satisfies($another) === false);
    });

    it('should check if a value satisfies class type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection(MyClass::class);

        $instance = new MyClass();
        $another = new MyInstanceMethodCallable();

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies(MyInterface::class) === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
        assert($type->satisfies($instance) === true);
        assert($type->satisfies($another) === false);
    });

    it('should check if a value satisfies `self` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('self', MyClass::class);

        $instance = new MyClass();
        $another = new MyInstanceMethodCallable();

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies(MyInterface::class) === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
        assert($type->satisfies($instance) === true);
        assert($type->satisfies($another) === false);
    });

    it('should check if a value satisfies `parent` type declaration', function () use ($closure, $iterator) {
        $type = new TypeReflection('parent', MyExtendedClass::class);

        $instance = new MyExtendedClass();
        $parent = new MyClass();
        $another = new MyInstanceMethodCallable();

        assert($type->satisfies(true) === false);
        assert($type->satisfies(false) === false);
        assert($type->satisfies(null) === false);
        assert($type->satisfies('') === false);
        assert($type->satisfies('is_object') === false);
        assert($type->satisfies(MyInterface::class) === false);
        assert($type->satisfies([]) === false);
        assert($type->satisfies($closure) === false);
        assert($type->satisfies($iterator) === false);
        assert($type->satisfies(1) === false);
        assert($type->satisfies(1.0) === false);
        assert($type->satisfies(2.5) === false);
        assert($type->satisfies((object) []) === false);
        assert($type->satisfies($instance) === true);
        assert($type->satisfies($parent) === true);
        assert($type->satisfies($another) === false);
    });
});
