<?php

declare(strict_types=1);

use Technically\CallableReflection\CallableReflection;

describe('CallableReflection::apply()', function () {
    it('should call reflected callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        assert($reflection->apply() === 'hello');
    });

    it('it should reflected callable with numeric arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->apply(['Jordi']) === 'Hello, Jordi!');
        assert($reflection->apply(['Spok', 'Live and prosper']) === 'Live and prosper, Spok!');
    });

    it('it should reflected callable with named arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->apply(['name' => 'Jordi']) === 'Hello, Jordi!');
        assert($reflection->apply(['name' => 'Spok', 'greeting' => 'Live and prosper']) === 'Live and prosper, Spok!');
    });

    it('it should reflected callable with mixed arguments array', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        assert($reflection->apply(['Jordi']) === 'Hello, Jordi!');
        assert($reflection->apply(['Spok', 'greeting' => 'Live and prosper']) === 'Live and prosper, Spok!');
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
        assert($error->getMessage() === "Too few arguments: argument #0 (`name`) is expected, but not passed.");
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
        assert($error->getMessage() ===
            "Too many arguments: unused extra arguments passed: #3. This may be a mistake in your code.");
    });

    it('it should throw ArgumentsCountError when unnecessary extra named arguments passed', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string $greeting = 'Hello') {
            return "{$greeting}, {$name}!";
        });

        try {
            $reflection->apply(['Jim', 'greeeeting' => 'Wat?!']);
        } catch (Throwable $error) {
            // passthru
        }

        assert(isset($error));
        assert($error instanceof ArgumentCountError);
        assert($error->getMessage() ===
            "Too many arguments: unused extra arguments passed: `greeeeting`. This may be a mistake in your code.");
    });
});
