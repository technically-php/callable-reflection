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

    it('it should call reflected callable with arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->call('Jordi') === 'Hello, Jordi!');
        assert($reflection->call('Spok', 'Live and prosper') === 'Live and prosper, Spok!');
    });

    if (PHP_VERSION_ID >= 80000) {
        it('it should call reflected callable with arguments passing named arguments', function () {
            $reflection = CallableReflection::fromCallable(
                function (string $name = 'dude', string $greeting = 'Hello') {
                    return "{$greeting}, {$name}!";
                }
            );

            /** @noinspection PhpLanguageLevelInspection */
            assert($reflection->call(name: 'Jordi') === 'Hello, Jordi!');
            /** @noinspection PhpLanguageLevelInspection */
            assert($reflection->call(greeting: 'Whatsup') === 'Whatsup, dude!');
        });
    }

    it('it should call reflected callable with variadic arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        assert($reflection->call() === 'Hello!');
        assert($reflection->call('Hello') === 'Hello!');
        assert($reflection->call('Live and prosper', 'Spok') === 'Live and prosper, Spok!');
        assert($reflection->call('Live and prosper', 'Captain', 'Spok') === 'Live and prosper, Captain, Spok!');
    });

    if (PHP_VERSION_ID >= 80000) {
        it('it should call reflected callable with variadic arguments passing named arguments', function () {
            $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ...$names) {
                return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
            });

            assert($reflection->call(...['greeting' => 'Hello']) === 'Hello!');
            assert($reflection->call(...['names' =>['Spok', 'Captain']]) === 'Hello, Spok, Captain!');
        });

        it('it should call reflected callable with variadic arguments passing extra named arguments', function () {
            $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ...$names) {
                return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
            });

            assert($reflection->call(...['greeting' => 'Hello']) === 'Hello!');
            assert($reflection->call(...['officer' => 'Spok', 'captain' => 'Picard']) === 'Hello, Spok, Picard!');
        });
    }
});
