![Status][badge]

# Technically Callable Reflection

`Technically\CallableReflection` is a handy library to simplify reflecting any `callable`.
It provides a unified interface for reading parameters for any callable, 
seamlessly supporting PHP8 union types as well. 

## Features

- Unified and simplified interface to reflect callable parameters 
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

$reflection = new CallableReflection($function);

var_dump($reflection->isFunction()); // false
var_dump($reflection->isMethod()); // false
var_dump($reflection->isClosure()); // true

[$p1, $p2] = $reflection->getParameters();

var_dump($p2->getName()); // factory
var_dump($p2->isNullable()); // true
var_dump($p2->isOptional()); // false
var_dump($p2->hasTypes()); // true

[$t1, $t2] = $p2->getTypes();

var_dump($t1->isScalar()); // false 
var_dump($t1->isClassName()); // true 
var_dump($t1->getType()); // "Closure" 

var_dump($t2->isScalar()); // true 
var_dump($t2->isClassName()); // false 
var_dump($t2->getType()); // "string" 
```

### Invoking callable via reflection

```php
$function = function (string $abstract, Closure|string|null $concrete): mixed {
// function body
};

$reflection = new CallableReflection($function);

// 1) call by passing positional parameters
$result = $reflection->call(LoggerInterface::class, MyLogger::class);

// 2) call by passing positional parameters array 
$result = $reflection->apply([LoggerInterface::class, MyLogger::class]);

// 3) call by passing named parameters array 
$result = $reflection->apply(['concrete' => MyLogger::class, 'abstract' => LoggerInterface::class]);

// 4) call by passing mixed named and positional parameters array 
$result = $reflection->apply([LoggerInterface::class, 'concrete' => MyLogger::class]);

// 5) CallableReflection is a callable by itself
$result = $reflection(LoggerInterface::class, MyLogger::class);
```

## Changelog

All notable changes to this project will be documented in the [CHANGELOG](./CHANGELOG.md) file.


## Credits

Implemented by [Ivan Voskoboinyk][3]

[1]: https://www.php-fig.org/psr/psr-11/
[2]: https://getcomposer.org/
[3]: https://github.com/e1himself?utm_source=web&utm_medium=github&utm_campaign=technically/callable-reflection
[badge]: https://github.com/technically-php/callable-reflection/actions/workflows/test.yml/badge.svg
