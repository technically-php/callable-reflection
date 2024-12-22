![Status][badge]

# Technically Callable Reflection

`Technically\CallableReflection` is a handy library to simplify reflecting any `callable`.
It provides a unified interface for reading callable arguments seamlessly supporting PHP8 union types.
It also lets you to easily invoke a callable with `call()` and `apply()` supporting named parameters.

## Features

- Unified and simplified interface to reflect callable parameters 
- No dependencies
- PHP 8.0 ready (supports union type hints; see examples below)
- PHP 7.1+ compatible
- Semver
- Tests

## Installation

Use [Composer][2] package manager to add *Technically\CallableReflection* to your project:

```
composer require technically/callable-reflection
```

## Examples

### Reflecting callable properties

```php
<?php
$function = function (string $abstract, Closure|string|null $concrete): mixed {
    // function body
};

$reflection = CallableReflection::fromCallable($function);

var_dump($reflection->isFunction()); // false
var_dump($reflection->isMethod()); // false
var_dump($reflection->isClosure()); // true

[$p1, $p2] = $reflection->getParameters();

var_dump($p2->getName()); // "concrete"
var_dump($p2->isNullable()); // true
var_dump($p2->isOptional()); // false
var_dump($p2->hasTypes()); // true

[$t1, $t2, $t3] = $p2->getTypes();

var_dump($t1->isScalar()); // false 
var_dump($t1->isClassName()); // true 
var_dump($t1->getType()); // "Closure" 

var_dump($t2->isScalar()); // true 
var_dump($t2->isClassName()); // false 
var_dump($t2->getType()); // "string"

var_dump($t3->isNull()); // true
var_dump($t3->isScalar()); // false 
var_dump($t3->isClassName()); // false 
var_dump($t3->getType()); // "null" 
```

### Reflecting arbitrary class constructor

```php
<?php
final class MyService {
    public function __construct(LoggerInterface $logger, bool $debug = false)
    {
        // ...
    }
}

$reflection = CallableReflection::fromConstructor(MyService::class);

var_dump($reflection->isConstructor()); // true
var_dump($reflection->isFunction()); // false
var_dump($reflection->isMethod()); // false
var_dump($reflection->isClosure()); // false

[$p1, $p2] = $reflection->getParameters();

var_dump($p1->getName()); // "logger"
var_dump($p1->isNullable()); // false
var_dump($p1->isOptional()); // false
var_dump($p1->hasTypes()); // true

var_dump($p2->getName()); // "debug"
var_dump($p2->isNullable()); // false
var_dump($p2->isOptional()); // true
var_dump($p2->hasTypes()); // true
```

### Checking if value satisfies parameter type declaration

```php
$function = function (int|string $value = null): mixed {
    // function body
};

$reflection = CallableReflection::fromCallable($function);

[$param] = $reflection->getParameters();

var_dump($param->satisfies(null)); // true
var_dump($param->satisfies(1)); // true
var_dump($param->satisfies('Hello')); // true
var_dump($param->satisfies(2.5)); // false
var_dump($param->satisfies([])); // false
var_dump($param->satisfies(true)); // false
```

### Invoking callable via reflection

```php
$function = function (string $abstract, Closure|string|null $concrete): mixed {
// function body
};

$reflection = CallableReflection::fromCallable($function);

// 1) call with positional parameters
$result = $reflection->call(LoggerInterface::class, MyLogger::class);

// 1) call with named parameters
$result = $reflection->call(concrete: MyLogger::class, abstract: LoggerInterface::class);

// 2) call with positional parameters array 
$result = $reflection->apply([LoggerInterface::class, MyLogger::class]);

// 3) call with named parameters array 
$result = $reflection->apply(['concrete' => MyLogger::class, 'abstract' => LoggerInterface::class]);

// 4) call with mixed named and positional parameters array 
$result = $reflection->apply([LoggerInterface::class, 'concrete' => MyLogger::class]);

// 5) CallableReflection is a callable by itself
$result = $reflection(LoggerInterface::class, MyLogger::class);
```

### Invoking constructor via reflection

```php
final class MyService {
    public function __construct(LoggerInterface $logger, bool $debug = false)
    {
        // ...
    }
}

$reflection = CallableReflection::fromConstructor(MyService::class);

$service = $reflection->call(new NullLogger());
// or alternatively:
// $service = $reflection->apply(['logger' => new NullLogger()]);
assert($service instanceof MyService);
```

## How is this better than reflecting `Closure::fromCallable()`

This library's functionality is somewhat similar to what a reflection of the standard `Closure::fromCallable()` can provide. 
And if that's sufficient for your use case, I recommend using the standard code.

This library, however, does provide some added value on top:

- It unifies interactions will all kinds of callables, including class constructors.
  That was actually the primary use case I had in mind for it — building a DI service container.

  For example, you cannot instantiate a new instance with Closure::fromCallable():

  ```php
  $reflection = new ReflectionFunction(Closure::fromCallable([MyRemoteService::class, '__construct']));
  $service = $reflection->call($token); // (!) doesn't work
  ```

  but you can call a constructor with this library:

  ```php
  $reflection = CallableReflection::fromConstructor(MyRemoteService::class);
  $service = $reflection->call($token);
  ```

- It can know and tell what type of callable was reflected, while with Closure::fromCallable() this information is lost.
  This might be important for certain use cases, or not. Depending on the scenario.

- It has nice additional convenience getters and checkers. Like `satisfies()`, `isOptional()`, `isNull()`, `isScalar()`, etc.

- I also find the API somewhat more intuitive and convenient (which is, of course, subjective and debatable),
  as the native Reflection API is slightly polluted as a result of BC-preserving additions

- In terms of performance, I am sure this implementation will most definitely be slower than using the native code.
  Though I didn't test it to provide the exact numbers. It's basically doing almost the same, but with extra code on top
  — just operating on top of Reflections API.


## Changelog

All notable changes to this project will be documented in the [CHANGELOG](./CHANGELOG.md) file.


## Credits

Implemented by :space_invader: [Ivan Voskoboinyk][3].

[1]: https://www.php-fig.org/psr/psr-11/
[2]: https://getcomposer.org/
[3]: https://voskoboinyk.com/
[badge]: https://github.com/technically-php/callable-reflection/actions/workflows/test.yml/badge.svg
