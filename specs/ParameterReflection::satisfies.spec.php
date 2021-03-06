<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\ParameterReflection;
use Technically\CallableReflection\Parameters\TypeReflection;

describe('ParameterReflection::satisfies()', function () {
    it('should check if a value satisfies union type declaration', function () {
        $parameter = new ParameterReflection('whatever', [
            new TypeReflection('int'),
            new TypeReflection('string'),
            new TypeReflection('array'),
        ]);

        assert($parameter->satisfies(true) === false);
        assert($parameter->satisfies(false) === false);
        assert($parameter->satisfies(null) === false);
        assert($parameter->satisfies('') === true);
        assert($parameter->satisfies('is_object') === true);
        assert($parameter->satisfies([]) === true);
        assert($parameter->satisfies(1) === true);
        assert($parameter->satisfies(1.0) === false);
        assert($parameter->satisfies(2.5) === false);
        assert($parameter->satisfies((object) []) === false);
    });

    it('should return true for null if parameter is nullable', function () {
        $parameter = new ParameterReflection('whatever', [new TypeReflection('int')], false, true);

        assert($parameter->satisfies(true) === false);
        assert($parameter->satisfies(false) === false);
        assert($parameter->satisfies(null) === true);
        assert($parameter->satisfies('') === false);
        assert($parameter->satisfies('is_object') === false);
        assert($parameter->satisfies([]) === false);
        assert($parameter->satisfies(1) === true);
        assert($parameter->satisfies(1.0) === false);
        assert($parameter->satisfies(2.5) === false);
        assert($parameter->satisfies((object) []) === false);
    });

    it('should treat parameters without type declarations as allow-everything', function () {
        $parameter = new ParameterReflection('whatever', []);

        assert($parameter->satisfies(true) === true);
        assert($parameter->satisfies(false) === true);
        assert($parameter->satisfies(null) === true);
        assert($parameter->satisfies('') === true);
        assert($parameter->satisfies('is_object') === true);
        assert($parameter->satisfies([]) === true);
        assert($parameter->satisfies(1) === true);
        assert($parameter->satisfies(1.0) === true);
        assert($parameter->satisfies(2.5) === true);
        assert($parameter->satisfies((object) []) === true);
    });

    it('should require array values for variadic parameters', function () {
        $parameter = new ParameterReflection(
            'names',
            [new TypeReflection('string')],
            $optional = true,
            $nullable = false,
            $variadic = true,
            $promoted = false,
            $default = []
        );

        assert($parameter->satisfies(true) === false);
        assert($parameter->satisfies(false) === false);
        assert($parameter->satisfies(null) === false);
        assert($parameter->satisfies('') === false);
        assert($parameter->satisfies('is_object') === false);
        assert($parameter->satisfies([]) === true);
        assert($parameter->satisfies(['x', 'y']) === true);
        assert($parameter->satisfies(['x', 5]) === false);
        assert($parameter->satisfies(1) === false);
        assert($parameter->satisfies([1, 2, 3]) === false);
        assert($parameter->satisfies(1.0) === false);
        assert($parameter->satisfies([1.0]) === false);
        assert($parameter->satisfies(2.5) === false);
        assert($parameter->satisfies((object) []) === false);
    });
});
