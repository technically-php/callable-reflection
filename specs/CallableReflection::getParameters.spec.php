<?php

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\CallableReflection;

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
});
