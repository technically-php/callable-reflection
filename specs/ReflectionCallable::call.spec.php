<?php

use Technically\ReflectionCallable\ReflectionCallable;

describe('ReflectionCallable::call()', function () {
    it('should call reflected callable without arguments', function () {
        $reflection = new ReflectionCallable(function () {
            return 'hello';
        });

        assert($reflection->call() === 'hello');
    });

    it('it should reflected callable with arguments', function () {
        $reflection = new ReflectionCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->call('Jordi') === 'Hello, Jordi!');
        assert($reflection->call('Spok', 'Live and prosper') === 'Live and prosper, Spok!');
    });
});
