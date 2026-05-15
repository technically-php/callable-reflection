<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;

describe('CallableReflection::call()', function () {
    it('should call reflected callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        expect($reflection->call())->toBe('hello');
    });

    it('it should call reflected callable with arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        expect($reflection->call('Jordi'))->toBe('Hello, Jordi!');
        expect($reflection->call('Spok', 'Live and prosper'))->toBe('Live and prosper, Spok!');
    });

    if (PHP_VERSION_ID >= 80000) {
        it('it should call reflected callable with arguments passing named arguments', function () {
            $reflection = CallableReflection::fromCallable(
                function (string $name = 'dude', string $greeting = 'Hello') {
                    return "{$greeting}, {$name}!";
                }
            );

            expect($reflection->call(...['name' => 'Jordi']))->toBe('Hello, Jordi!');
            expect($reflection->call(...['greeting' => 'Whatsup']))->toBe('Whatsup, dude!');
        });
    }

    it('it should call reflected callable with variadic arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        expect($reflection->call())->toBe('Hello!');
        expect($reflection->call('Hello'))->toBe('Hello!');
        expect($reflection->call('Live and prosper', 'Spok'))->toBe('Live and prosper, Spok!');
        expect($reflection->call('Live and prosper', 'Captain', 'Spok'))->toBe('Live and prosper, Captain, Spok!');
    });

    if (PHP_VERSION_ID >= 80000) {
        it('it should call reflected callable with variadic arguments passing named arguments', function () {
            $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ...$names) {
                return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
            });

            expect($reflection->call(...['greeting' => 'Hello']))->toBe('Hello!');
            expect($reflection->call(...['names' =>['Spok', 'Captain']]))->toBe('Hello, Spok, Captain!');
        });

        it('it should call reflected callable with variadic arguments passing extra named arguments', function () {
            $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ...$names) {
                return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
            });

            expect($reflection->call(...['greeting' => 'Hello']))->toBe('Hello!');
            expect($reflection->call(...['officer' => 'Spok', 'captain' => 'Picard']))->toBe('Hello, Spok, Picard!');
        });
    }
});
