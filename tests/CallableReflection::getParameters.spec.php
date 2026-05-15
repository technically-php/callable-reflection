<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Tests\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Tests\Fixtures\MyParentDependencyCallable;
use Technically\CallableReflection\Tests\Fixtures\MySelfDependencyCallable;

describe('CallableReflection::getParameters()', function () {
    it('should reflect arguments of callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        expect($reflection->getParameters())->toBe([]);
    });

    it('should reflect arguments of callable with type hints', function () {
        $reflection = CallableReflection::fromCallable(function (?int $i, string $a, string $b = 'B', ?string $c = null, $d = 1) {
            return null;
        });

        expect(count($reflection->getParameters()))->toBe(5);

        [$i, $a, $b, $c, $d] = $reflection->getParameters();

        expect($i->getName())->toBe('i');
        expect($i->isNullable())->toBeTrue();
        expect($i->isOptional())->toBeFalse();
        expect($i->isVariadic())->toBeFalse();
        expect($i->hasTypes())->toBeTrue();
        expect($i->getTypes())->toEqual([
            new TypeReflection('int'),
        ]);

        expect($a->getName())->toBe('a');
        expect($a->isNullable())->toBeFalse();
        expect($a->isOptional())->toBeFalse();
        expect($a->isVariadic())->toBeFalse();
        expect($a->hasTypes())->toBeTrue();
        expect($a->getTypes())->toEqual([
            new TypeReflection('string'),
        ]);

        expect($b->getName())->toBe('b');
        expect($b->isNullable())->toBeFalse();
        expect($b->isOptional())->toBeTrue();
        expect($b->isVariadic())->toBeFalse();
        expect($b->getDefaultValue())->toBe('B');
        expect($b->hasTypes())->toBeTrue();
        expect($b->getTypes())->toEqual([
            new TypeReflection('string'),
        ]);

        expect($c->getName())->toBe('c');
        expect($c->isNullable())->toBeTrue();
        expect($c->isOptional())->toBeTrue();
        expect($c->isVariadic())->toBeFalse();
        expect($c->getDefaultValue())->toBeNull();
        expect($c->hasTypes())->toBeTrue();
        expect($c->getTypes())->toEqual([
            new TypeReflection('string'),
        ]);

        expect($d->getName())->toBe('d');
        // Note: it's nullable because there are no type declarations. Anything is accepted, including null.
        expect($d->isNullable())->toBeTrue();
        expect($d->isOptional())->toBeTrue();
        expect($d->isVariadic())->toBeFalse();
        expect($d->getDefaultValue())->toBe(1);
        expect($d->hasTypes())->toBeFalse();
        expect($d->getTypes())->toBe([]);
    });

    if (PHP_MAJOR_VERSION >= 8) {
        it('should reflect arguments of callable with PHP8 union-type hints', function () {
            $reflection = CallableReflection::fromCallable(
                require __DIR__ . '/Fixtures/my_union_types_closure.php'
            );

            expect(count($reflection->getParameters()))->toBe(3);

            [$a, $b, $c] = $reflection->getParameters();

            expect($a->getName())->toBe('a');
            expect($a->isNullable())->toBeFalse();
            expect($a->isOptional())->toBeFalse();
            expect($a->isVariadic())->toBeFalse();
            expect($a->hasTypes())->toBeTrue();
            expect($a->getTypes())->toEqual([
                new TypeReflection('int'),
                new TypeReflection('false'),
            ]);

            expect($b->getName())->toBe('b');
            expect($b->isNullable())->toBeTrue();
            expect($b->isOptional())->toBeFalse();
            expect($b->isVariadic())->toBeFalse();
            expect($b->hasTypes())->toBeTrue();
            expect($b->getTypes())->toEqual([
                new TypeReflection('string'),
                new TypeReflection('int'),
                new TypeReflection('null'),
            ]);

            expect($c->getName())->toBe('c');
            expect($c->isNullable())->toBeTrue();
            expect($c->isOptional())->toBeTrue();
            expect($c->isVariadic())->toBeFalse();
            expect($c->getDefaultValue())->toBeNull();
            expect($c->hasTypes())->toBeTrue();
            expect($c->getTypes())->toEqual([
                new TypeReflection('Closure'),
                new TypeReflection('callable'),
                new TypeReflection('bool'),
                new TypeReflection('null'),
            ]);
        });
    }

    it('should parse self argument types', function () {
        $callable = new MySelfDependencyCallable();

        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect(count($reflection->getParameters()))->toBe(1);

        [$self] = $reflection->getParameters();

        expect($self->getName())->toBe('self');
        expect($self->isNullable())->toBeTrue();
        expect($self->isOptional())->toBeTrue();
        expect($self->isVariadic())->toBeFalse();
        expect($self->getDefaultValue())->toBeNull();
        expect($self->hasTypes())->toBeTrue();
        expect($self->getTypes())->toEqual([
            new TypeReflection(
                /**
                 * Since PHP 8.5 `self` type hint is resolved to the actual class name at compile time.
                 * So the Reflection API cannot tell if it was `self` or the actual class name in the source code.
                 * @see https://github.com/php/php-src/issues/21284
                 */
                PHP_VERSION_ID > 80500 ? MySelfDependencyCallable::class : 'self',
                MySelfDependencyCallable::class,
            ),
        ]);
    });

    it('should parse parent argument types', function () {
        $callable = new MyParentDependencyCallable();

        expect(is_callable($callable))->toBeTrue();

        $reflection = CallableReflection::fromCallable($callable);

        expect(count($reflection->getParameters()))->toBe(2);

        [$self, $parent] = $reflection->getParameters();

        expect($self->getName())->toBe('self');
        expect($self->isNullable())->toBeTrue();
        expect($self->isOptional())->toBeFalse();
        expect($self->isVariadic())->toBeFalse();
        expect($self->getDefaultValue())->toBeNull();
        expect($self->hasTypes())->toBeTrue();
        expect($self->getTypes())->toEqual([
            new TypeReflection(
                /**
                 * Since PHP 8.5 `self` type hint is resolved to the actual class name at compile time.
                 * So the Reflection API cannot tell if it was `self` or the actual class name in the source code.
                 * @see https://github.com/php/php-src/issues/21284
                 */
                PHP_VERSION_ID > 80500 ? MyParentDependencyCallable::class : 'self',
                MyParentDependencyCallable::class,
            ),
        ]);

        expect($parent->getName())->toBe('parent');
        expect($parent->isNullable())->toBeTrue();
        expect($parent->isOptional())->toBeTrue();
        expect($parent->isVariadic())->toBeFalse();
        expect($parent->getDefaultValue())->toBeNull();
        expect($parent->hasTypes())->toBeTrue();
        expect($parent->getTypes())->toEqual([
            new TypeReflection(
                /**
                 * Since PHP 8.5 `parent` type hint is resolved to the actual class name at compile time.
                 * So the Reflection API cannot tell if it was `parent` or the actual class name in the source code.
                 * @see https://github.com/php/php-src/issues/21284
                 */
                PHP_VERSION_ID > 80500 ? MyInstanceMethodCallable::class : 'parent',
                MyParentDependencyCallable::class,
            ),
        ]);
    });

    it('should support variadic parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string ... $aliases) {
            return array_merge([$name], $aliases);
        });

        expect(count($reflection->getParameters()))->toBe(2);

        [$name, $aliases] = $reflection->getParameters();

        expect($name->getName())->toBe('name');
        expect($name->isNullable())->toBeFalse();
        expect($name->isOptional())->toBeFalse();
        expect($name->isVariadic())->toBeFalse();
        expect($name->getDefaultValue())->toBeNull();
        expect($name->hasTypes())->toBeTrue();
        expect($name->getTypes())->toEqual([
            new TypeReflection('string'),
        ]);

        expect($aliases->getName())->toBe('aliases');
        expect($aliases->isNullable())->toBeFalse();
        expect($aliases->isOptional())->toBeTrue();
        expect($aliases->isVariadic())->toBeTrue();
        expect($aliases->getDefaultValue())->toBe([]);
        expect($aliases->hasTypes())->toBeTrue();
        expect($aliases->getTypes())->toEqual([
            new TypeReflection('string'),
        ]);
    });
});
