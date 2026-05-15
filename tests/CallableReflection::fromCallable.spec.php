<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Tests\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Tests\Fixtures\MyInvokableObjectCallable;
use Technically\CallableReflection\Tests\Fixtures\MyStaticMethodCallable;

describe('CallableReflection::fromCallable', function () {
    it('should reflect global functions by name', function () {
        require __DIR__ . '/Fixtures/my_global_function.php';

        $callable = 'my_global_function';
        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect($reflection->getCallable())->toBe($callable);
        expect($reflection->isFunction())->toBeTrue();
        expect($reflection->isClosure())->toBeFalse();
        expect($reflection->isMethod())->toBeFalse();
        expect($reflection->isStaticMethod())->toBeFalse();
        expect($reflection->isInstanceMethod())->toBeFalse();
        expect($reflection->isInvokableObject())->toBeFalse();
        expect($reflection->isConstructor())->toBeFalse();
    });

    it('should reflect Closures', function () {
        $callable = function () {
            return 'Hello';
        };
        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect($reflection->getCallable())->toBe($callable);
        expect($reflection->isFunction())->toBeFalse();
        expect($reflection->isClosure())->toBeTrue();
        expect($reflection->isMethod())->toBeFalse();
        expect($reflection->isStaticMethod())->toBeFalse();
        expect($reflection->isInstanceMethod())->toBeFalse();
        expect($reflection->isInvokableObject())->toBeFalse();
        expect($reflection->isConstructor())->toBeFalse();
    });

    it('should reflect static method array', function () {
        $callable = [MyStaticMethodCallable::class, 'hello'];
        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect($reflection->getCallable())->toBe($callable);
        expect($reflection->isFunction())->toBeFalse();
        expect($reflection->isClosure())->toBeFalse();
        expect($reflection->isMethod())->toBeTrue();
        expect($reflection->isStaticMethod())->toBeTrue();
        expect($reflection->isInstanceMethod())->toBeFalse();
        expect($reflection->isInvokableObject())->toBeFalse();
        expect($reflection->isConstructor())->toBeFalse();
    });

    it('should reflect static method string', function () {
        $callable = MyStaticMethodCallable::class .'::hello';
        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect($reflection->getCallable())->toBe($callable);
        expect($reflection->isFunction())->toBeFalse();
        expect($reflection->isClosure())->toBeFalse();
        expect($reflection->isMethod())->toBeTrue();
        expect($reflection->isStaticMethod())->toBeTrue();
        expect($reflection->isInstanceMethod())->toBeFalse();
        expect($reflection->isInvokableObject())->toBeFalse();
        expect($reflection->isConstructor())->toBeFalse();
    });

    it('should reflect instance methods array', function () {
        $callable = [new MyInstanceMethodCallable(), 'Hello'];
        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect($reflection->getCallable())->toBe($callable);
        expect($reflection->isFunction())->toBeFalse();
        expect($reflection->isClosure())->toBeFalse();
        expect($reflection->isMethod())->toBeTrue();
        expect($reflection->isStaticMethod())->toBeFalse();
        expect($reflection->isInstanceMethod())->toBeTrue();
        expect($reflection->isInvokableObject())->toBeFalse();
        expect($reflection->isConstructor())->toBeFalse();
    });

    it('should reflect invokable objects', function () {
        $callable = new MyInvokableObjectCallable();
        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect($reflection->getCallable())->toBe($callable);
        expect($reflection->isFunction())->toBeFalse();
        expect($reflection->isClosure())->toBeFalse();
        expect($reflection->isMethod())->toBeFalse();
        expect($reflection->isStaticMethod())->toBeFalse();
        expect($reflection->isInstanceMethod())->toBeFalse();
        expect($reflection->isInvokableObject())->toBeTrue();
        expect($reflection->isConstructor())->toBeFalse();
    });

    it('should by callable by itself', function () {
        $reflection = CallableReflection::fromCallable(function (string $name) {
            return "Hello {$name}!";
        });

        is_callable($reflection);

        expect($reflection('Captain'))->toBe('Hello Captain!');
    });

    it('should reflect callable with variadic parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string ...$names) {
            return sprintf('Hello %s!', implode(', ', $names));
        });

        is_callable($reflection);

        expect($reflection('Captain', 'Data', 'Commander'))->toBe('Hello Captain, Data, Commander!');
    });
});
