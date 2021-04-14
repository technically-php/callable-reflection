<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Specs\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Specs\Fixtures\MyInvokableObjectCallable;
use Technically\CallableReflection\Specs\Fixtures\MyStaticMethodCallable;

describe('CallableReflection::fromCallable', function () {
    it('should reflect global functions by name', function () {
        require __DIR__ . '/Fixtures/my_global_function.php';

        $callable = 'my_global_function';
        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === true);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === false);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
        assert($reflection->isConstructor() === false);
    });

    it('should reflect Closures', function () {
        $callable = function () {
            return 'Hello';
        };
        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === true);
        assert($reflection->isMethod() === false);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
        assert($reflection->isConstructor() === false);
    });

    it('should reflect static method array', function () {
        $callable = [MyStaticMethodCallable::class, 'hello'];
        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === true);
        assert($reflection->isStaticMethod() === true);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
        assert($reflection->isConstructor() === false);
    });

    it('should reflect static method string', function () {
        $callable = MyStaticMethodCallable::class .'::hello';
        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === true);
        assert($reflection->isStaticMethod() === true);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
        assert($reflection->isConstructor() === false);
    });

    it('should reflect instance methods array', function () {
        $callable = [new MyInstanceMethodCallable(), 'Hello'];
        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === true);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === true);
        assert($reflection->isInvokableObject() === false);
        assert($reflection->isConstructor() === false);
    });

    it('should reflect invokable objects', function () {
        $callable = new MyInvokableObjectCallable();
        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === false);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === true);
        assert($reflection->isConstructor() === false);
    });

    it('should by callable by itself', function () {
        $reflection = CallableReflection::fromCallable(function (string $name) {
            return "Hello {$name}!";
        });

        is_callable($reflection);

        assert($reflection('Captain') === 'Hello Captain!');
    });

    it('should reflect callable with variadic parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string ...$names) {
            return sprintf('Hello %s!', implode(', ', $names));
        });

        is_callable($reflection);

        assert($reflection('Captain', 'Data', 'Commander') === 'Hello Captain, Data, Commander!');
    });
});
