<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;

describe('CallableReflection::call()', function () {
    it('should call reflected callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        assert($reflection->call() === 'hello');
    });

    it('it should reflected callable with arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->call('Jordi') === 'Hello, Jordi!');
        assert($reflection->call('Spok', 'Live and prosper') === 'Live and prosper, Spok!');
    });
});
