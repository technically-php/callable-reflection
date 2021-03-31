<?php

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Specs\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Specs\Fixtures\MyInvokableObjectCallable;
use Technically\CallableReflection\Specs\Fixtures\MyStaticMethodCallable;

describe('CallableReflection', function () {
    it('should reflect global functions by name', function () {
        require __DIR__ . '/Fixtures/my_global_function.php';

        $callable = 'my_global_function';
        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === true);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === false);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
    });

    it('should reflect Closures', function () {
        $callable = function () {
            return 'Hello';
        };
        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === true);
        assert($reflection->isMethod() === false);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
    });

    it('should reflect static method array', function () {
        $callable = [MyStaticMethodCallable::class, 'hello'];
        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === true);
        assert($reflection->isStaticMethod() === true);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
    });

    it('should reflect static method string', function () {
        $callable = MyStaticMethodCallable::class .'::hello';
        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === true);
        assert($reflection->isStaticMethod() === true);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === false);
    });

    it('should reflect instance methods array', function () {
        $callable = [new MyInstanceMethodCallable(), 'Hello'];
        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === true);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === true);
        assert($reflection->isInvokableObject() === false);
    });

    it('should reflect invokable objects', function () {
        $callable = new MyInvokableObjectCallable();
        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert($reflection->getCallable() === $callable);
        assert($reflection->isFunction() === false);
        assert($reflection->isClosure() === false);
        assert($reflection->isMethod() === false);
        assert($reflection->isStaticMethod() === false);
        assert($reflection->isInstanceMethod() === false);
        assert($reflection->isInvokableObject() === true);
    });

    it('should by callable by itself', function () {
        $reflection = new CallableReflection(function (string $name) {
            return "Hello {$name}!";
        });

        is_callable($reflection);

        assert($reflection('Captain') === 'Hello Captain!');
    });
});
