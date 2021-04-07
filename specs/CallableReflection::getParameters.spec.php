<?php

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Specs\Fixtures\MyParentDependencyCallable;
use Technically\CallableReflection\Specs\Fixtures\MySelfDependencyCallable;
use Technically\CallableReflection\Specs\Fixtures\myuniontypesclosure;

describe('CallableReflection::getParameters()', function () {
    it('should reflect arguments of callable without arguments', function () {
        $reflection = new CallableReflection(function () {
            return 'hello';
        });

        assert($reflection->getParameters() === []);
    });

    it('should reflect arguments of callable with type hints', function () {
        $reflection = new CallableReflection(function (?int $i, string $a, string $b = 'B', string $c = null, $d = 1) {
            return null;
        });

        assert(count($reflection->getParameters()) === 5);

        [$i, $a, $b, $c, $d] = $reflection->getParameters();

        assert($i->getName() === 'i');
        assert($i->isNullable() === true);
        assert($i->isOptional() === false);
        assert($i->hasTypeDeclarations() === true);
        assert($i->getTypeDeclarations() == [
            new TypeReflection('int'),
        ]);

        assert($a->getName() === 'a');
        assert($a->isNullable() === false);
        assert($a->isOptional() === false);
        assert($a->hasTypeDeclarations() === true);
        assert($a->getTypeDeclarations() == [
            new TypeReflection('string'),
        ]);

        assert($b->getName() === 'b');
        assert($b->isNullable() === false);
        assert($b->isOptional() === true);
        assert($b->getDefaultValue() === 'B');
        assert($b->hasTypeDeclarations() === true);
        assert($b->getTypeDeclarations() == [
            new TypeReflection('string'),
        ]);

        assert($c->getName() === 'c');
        assert($c->isNullable() === true);
        assert($c->isOptional() === true);
        assert($c->getDefaultValue() === null);
        assert($c->hasTypeDeclarations() === true);
        assert($c->getTypeDeclarations() == [
            new TypeReflection('string'),
        ]);

        assert($d->getName() === 'd');
        // Note: it's nullable because there are no type declarations. Anything is accepted, including null.
        assert($d->isNullable() === true);
        assert($d->isOptional() === true);
        assert($d->getDefaultValue() === 1);
        assert($d->hasTypeDeclarations() === false);
        assert($d->getTypeDeclarations() === []);
    });

    if (PHP_MAJOR_VERSION >= 8) {
        it('should reflect arguments of callable with PHP8 union-type hints', function () {
            $reflection = new CallableReflection(
                require __DIR__ . '/Fixtures/my_union_types_closure.php'
            );

            assert(count($reflection->getParameters()) === 3);

            [$a, $b, $c] = $reflection->getParameters();

            assert($a->getName() === 'a');
            assert($a->isNullable() === false);
            assert($a->isOptional() === false);
            assert($a->hasTypeDeclarations() === true);
            assert($a->getTypeDeclarations() == [
                new TypeReflection('int'),
                new TypeReflection('false'),
            ]);

            assert($b->getName() === 'b');
            assert($b->isNullable() === true);
            assert($b->isOptional() === false);
            assert($b->hasTypeDeclarations() === true);
            assert($b->getTypeDeclarations() == [
                new TypeReflection('string'),
                new TypeReflection('int'),
                // Note: `null` is intentionally skipped, as it already contributed to `->isNullable() === true`
            ]);

            assert($c->getName() === 'c');
            assert($c->isNullable() === true);
            assert($c->isOptional() === true);
            assert($c->getDefaultValue() === null);
            assert($c->hasTypeDeclarations() === true);
            assert($c->getTypeDeclarations() == [
                new TypeReflection('Closure'),
                new TypeReflection('callable'),
                new TypeReflection('bool'),
            ]);
        });
    }

    it('should parse self argument types', function () {
        $callable = new MySelfDependencyCallable();

        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert(count($reflection->getParameters()) === 1);

        [$self] = $reflection->getParameters();

        assert($self->getName() === 'self');
        assert($self->isNullable() === true);
        assert($self->isOptional() === true);
        assert($self->getDefaultValue() === null);
        assert($self->hasTypeDeclarations() === true);
        assert($self->getTypeDeclarations() == [
            new TypeReflection('self', MySelfDependencyCallable::class),
        ]);
    });

    it('should parse parent argument types', function () {
        $callable = new MyParentDependencyCallable();

        assert(is_callable($callable));

        $reflection = new CallableReflection($callable);

        assert(count($reflection->getParameters()) === 2);

        [$self, $parent] = $reflection->getParameters();

        assert($self->getName() === 'self');
        assert($self->isNullable() === true);
        assert($self->isOptional() === false);
        assert($self->getDefaultValue() === null);
        assert($self->hasTypeDeclarations() === true);
        assert($self->getTypeDeclarations() == [
            new TypeReflection('self', MyParentDependencyCallable::class),
        ]);

        assert($parent->getName() === 'parent');
        assert($parent->isNullable() === true);
        assert($parent->isOptional() === true);
        assert($parent->getDefaultValue() === null);
        assert($parent->hasTypeDeclarations() === true);
        assert($parent->getTypeDeclarations() == [
            new TypeReflection('parent', MyParentDependencyCallable::class),
        ]);
    });
});
