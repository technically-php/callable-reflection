<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;

describe('CallableReflection::apply()', function () {
    it('should call reflected callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        assert($reflection->apply([]) === 'hello');
    });

    it('it should reflected callable with numeric arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->apply(['Jordi']) === 'Hello, Jordi!');
        assert($reflection->apply(['Spock', 'Live and prosper']) === 'Live and prosper, Spock!');
    });

    it('it should reflected callable with named arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->apply(['name' => 'Jordi']) === 'Hello, Jordi!');
        assert($reflection->apply(['name' => 'Spock', 'greeting' => 'Live and prosper']) === 'Live and prosper, Spock!');
    });

    it('it should reflected callable with mixed arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->apply(['Jordi']) === 'Hello, Jordi!');
        assert($reflection->apply(['Spock', 'greeting' => 'Live and prosper']) === 'Live and prosper, Spock!');
    });

    it('it should call reflected callable with variadic arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        assert($reflection->apply([]) === 'Hello!');
        assert($reflection->apply(['Hello']) === 'Hello!');
        assert($reflection->apply(['Live and prosper', 'Spock']) === 'Live and prosper, Spock!');
        assert($reflection->apply(['Live and prosper', 'Captain', 'Spock']) === 'Live and prosper, Captain, Spock!');
    });

    it('it should call reflected callable with variadic arguments by passing named parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        assert($reflection->apply(['Salute', 'names' => ['Captain', 'Spock']]) === 'Salute, Captain, Spock!');
    });

    it('it should call reflected callable with variadic argument catching unknown named parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        assert(
            $reflection->apply(['Salute', 'Picard' => 'Captain', 'officer' => 'Spock']) === 'Salute, Captain, Spock!'
        );
    });

    it('it should throw ArgumentsCountError when too few arguments passed', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        try {
            $reflection->apply([]);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof ArgumentCountError);
        assert($error->getMessage() === "Too few arguments: Argument #1 (`name`) is not passed.");
    });

    it('it should throw ArgumentsCountError when unnecessary extra arguments passed', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        try {
            $reflection->apply(['Jim', 'Hi', 'Wat?!']);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof ArgumentCountError);
        assert($error->getMessage() === "Too many arguments: unexpected extra argument passed: #2.");
    });

    it('it should throw Error when unknown named argument passed', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        try {
            $reflection->apply(['Jim', 'greeeeting' => 'Wat?!']);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof Error);
        assert($error->getMessage() === 'Unknown named parameter `greeeeting`.');
    });

    it('it should throw Error when positional argument passed after named argument', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        try {
            $reflection->apply(['greeting' => 'Wat?!', 'Jim']);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof Error);
        assert($error->getMessage() === 'Cannot use positional argument after named argument.');
    });

    it('it should throw Error when named parameter overwrites previous positional argument value', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        try {
            $reflection->apply(['Jim', 'name' => 'not Jim']);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof Error);
        assert($error->getMessage() === 'Named parameter `name` overwrites positional argument.');
    });

    it('it should throw Error when there is named parameter for variadic argument and unknown named parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        try {
            $reflection->apply(['Salute', 'names' => ['Spock'], 'officer' => 'Spock']);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof Error);
        assert($error->getMessage() === 'Unknown named parameter `officer`.');
    });
});
