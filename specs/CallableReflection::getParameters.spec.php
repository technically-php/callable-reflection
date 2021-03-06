<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Specs\Fixtures\MyParentDependencyCallable;
use Technically\CallableReflection\Specs\Fixtures\MySelfDependencyCallable;

describe('CallableReflection::getParameters()', function () {
    it('should reflect arguments of callable without arguments', function () {
        $reflection = CallableReflection::fromCallable(function () {
            return 'hello';
        });

        assert($reflection->getParameters() === []);
    });

    it('should reflect arguments of callable with type hints', function () {
        $reflection = CallableReflection::fromCallable(function (?int $i, string $a, string $b = 'B', string $c = null, $d = 1) {
            return null;
        });

        assert(count($reflection->getParameters()) === 5);

        [$i, $a, $b, $c, $d] = $reflection->getParameters();

        assert($i->getName() === 'i');
        assert($i->isNullable() === true);
        assert($i->isOptional() === false);
        assert($i->isVariadic() === false);
        assert($i->hasTypes() === true);
        assert($i->getTypes() == [
            new TypeReflection('int'),
        ]);

        assert($a->getName() === 'a');
        assert($a->isNullable() === false);
        assert($a->isOptional() === false);
        assert($a->isVariadic() === false);
        assert($a->hasTypes() === true);
        assert($a->getTypes() == [
            new TypeReflection('string'),
        ]);

        assert($b->getName() === 'b');
        assert($b->isNullable() === false);
        assert($b->isOptional() === true);
        assert($b->isVariadic() === false);
        assert($b->getDefaultValue() === 'B');
        assert($b->hasTypes() === true);
        assert($b->getTypes() == [
            new TypeReflection('string'),
        ]);

        assert($c->getName() === 'c');
        assert($c->isNullable() === true);
        assert($c->isOptional() === true);
        assert($c->isVariadic() === false);
        assert($c->getDefaultValue() === null);
        assert($c->hasTypes() === true);
        assert($c->getTypes() == [
            new TypeReflection('string'),
        ]);

        assert($d->getName() === 'd');
        // Note: it's nullable because there are no type declarations. Anything is accepted, including null.
        assert($d->isNullable() === true);
        assert($d->isOptional() === true);
        assert($d->isVariadic() === false);
        assert($d->getDefaultValue() === 1);
        assert($d->hasTypes() === false);
        assert($d->getTypes() === []);
    });

    if (PHP_MAJOR_VERSION >= 8) {
        it('should reflect arguments of callable with PHP8 union-type hints', function () {
            $reflection = CallableReflection::fromCallable(
                require __DIR__ . '/Fixtures/my_union_types_closure.php'
            );

            assert(count($reflection->getParameters()) === 3);

            [$a, $b, $c] = $reflection->getParameters();

            assert($a->getName() === 'a');
            assert($a->isNullable() === false);
            assert($a->isOptional() === false);
            assert($a->isVariadic() === false);
            assert($a->hasTypes() === true);
            assert($a->getTypes() == [
                new TypeReflection('int'),
                new TypeReflection('false'),
            ]);

            assert($b->getName() === 'b');
            assert($b->isNullable() === true);
            assert($b->isOptional() === false);
            assert($b->isVariadic() === false);
            assert($b->hasTypes() === true);
            assert($b->getTypes() == [
                new TypeReflection('string'),
                new TypeReflection('int'),
                new TypeReflection('null'),
            ]);

            assert($c->getName() === 'c');
            assert($c->isNullable() === true);
            assert($c->isOptional() === true);
            assert($c->isVariadic() === false);
            assert($c->getDefaultValue() === null);
            assert($c->hasTypes() === true);
            assert($c->getTypes() == [
                new TypeReflection('Closure'),
                new TypeReflection('callable'),
                new TypeReflection('bool'),
                new TypeReflection('null'),
            ]);
        });
    }

    it('should parse self argument types', function () {
        $callable = new MySelfDependencyCallable();

        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert(count($reflection->getParameters()) === 1);

        [$self] = $reflection->getParameters();

        assert($self->getName() === 'self');
        assert($self->isNullable() === true);
        assert($self->isOptional() === true);
        assert($self->isVariadic() === false);
        assert($self->getDefaultValue() === null);
        assert($self->hasTypes() === true);
        assert($self->getTypes() == [
            new TypeReflection('self', MySelfDependencyCallable::class),
        ]);
    });

    it('should parse parent argument types', function () {
        $callable = new MyParentDependencyCallable();

        assert(is_callable($callable));

        $reflection = CallableReflection::fromCallable($callable);

        assert(count($reflection->getParameters()) === 2);

        [$self, $parent] = $reflection->getParameters();

        assert($self->getName() === 'self');
        assert($self->isNullable() === true);
        assert($self->isOptional() === false);
        assert($self->isVariadic() === false);
        assert($self->getDefaultValue() === null);
        assert($self->hasTypes() === true);
        assert($self->getTypes() == [
            new TypeReflection('self', MyParentDependencyCallable::class),
        ]);

        assert($parent->getName() === 'parent');
        assert($parent->isNullable() === true);
        assert($parent->isOptional() === true);
        assert($parent->isVariadic() === false);
        assert($parent->getDefaultValue() === null);
        assert($parent->hasTypes() === true);
        assert($parent->getTypes() == [
            new TypeReflection('parent', MyParentDependencyCallable::class),
        ]);
    });

    it('should support variadic parameters', function () {
        $reflection = CallableReflection::fromCallable(function (string $name, string ... $aliases) {
            return array_merge([$name], $aliases);
        });

        assert(count($reflection->getParameters()) === 2);

        [$name, $aliases] = $reflection->getParameters();

        assert($name->getName() === 'name');
        assert($name->isNullable() === false);
        assert($name->isOptional() === false);
        assert($name->isVariadic() === false);
        assert($name->getDefaultValue() === null);
        assert($name->hasTypes() === true);
        assert($name->getTypes() == [
            new TypeReflection('string'),
        ]);

        assert($aliases->getName() === 'aliases');
        assert($aliases->isNullable() === false);
        assert($aliases->isOptional() === true);
        assert($aliases->isVariadic() === true);
        assert($aliases->getDefaultValue() === []);
        assert($aliases->hasTypes() === true);
        assert($aliases->getTypes() == [
            new TypeReflection('string'),
        ]);
    });
});
