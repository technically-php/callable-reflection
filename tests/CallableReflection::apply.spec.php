<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;

describe('CallableReflection::apply()', function () {
    it('should call reflected callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        expect($reflection->apply([]))->toBe('hello');
    });

    it('it should reflected callable with numeric arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        expect($reflection->apply(['Jordi']))->toBe('Hello, Jordi!');
        expect($reflection->apply(['Spock', 'Live and prosper']))->toBe('Live and prosper, Spock!');
    });

    it('it should reflected callable with named arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        expect($reflection->apply(['name' => 'Jordi']))->toBe('Hello, Jordi!');
        expect($reflection->apply(['name' => 'Spock', 'greeting' => 'Live and prosper']))->toBe('Live and prosper, Spock!');
    });

    it('it should reflected callable with mixed arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        expect($reflection->apply(['Jordi']))->toBe('Hello, Jordi!');
        expect($reflection->apply(['Spock', 'greeting' => 'Live and prosper']))->toBe('Live and prosper, Spock!');
    });

    it('it should call reflected callable with variadic arguments', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        expect($reflection->apply([]))->toBe('Hello!');
        expect($reflection->apply(['Hello']))->toBe('Hello!');
        expect($reflection->apply(['Live and prosper', 'Spock']))->toBe('Live and prosper, Spock!');
        expect($reflection->apply(['Live and prosper', 'Captain', 'Spock']))->toBe('Live and prosper, Captain, Spock!');
    });

    it('it should call reflected callable with variadic arguments by passing named parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        expect($reflection->apply(['Salute', 'names' => ['Captain', 'Spock']]))->toBe('Salute, Captain, Spock!');
    });

    it('it should call reflected callable with variadic argument catching unknown named parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $greeting = 'Hello', string ... $names) {
            return $greeting . ($names ? ', ' . implode(', ', $names) : '') . '!';
        });

        expect($reflection->apply(['Salute', 'Picard' => 'Captain', 'officer' => 'Spock']))->toBe('Salute, Captain, Spock!');
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

        expect(isset($error))->toBeTrue();
        expect($error)->toBeInstanceOf(ArgumentCountError::class);
        expect($error->getMessage())->toBe("Too few arguments: Argument #1 (`name`) is not passed.");
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

        expect(isset($error))->toBeTrue();
        expect($error)->toBeInstanceOf(ArgumentCountError::class);
        expect($error->getMessage())->toBe("Too many arguments: unexpected extra argument passed: #2.");
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

        expect(isset($error))->toBeTrue();
        expect($error)->toBeInstanceOf(Error::class);
        expect($error->getMessage())->toBe('Unknown named parameter `greeeeting`.');
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

        expect(isset($error))->toBeTrue();
        expect($error)->toBeInstanceOf(Error::class);
        expect($error->getMessage())->toBe('Cannot use positional argument after named argument.');
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

        expect(isset($error))->toBeTrue();
        expect($error)->toBeInstanceOf(Error::class);
        expect($error->getMessage())->toBe('Named parameter `name` overwrites positional argument.');
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

        expect(isset($error))->toBeTrue();
        expect($error)->toBeInstanceOf(Error::class);
        expect($error->getMessage())->toBe('Unknown named parameter `officer`.');
    });
});
