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

        expect($parameter->satisfies(true))->toBeFalse();
        expect($parameter->satisfies(false))->toBeFalse();
        expect($parameter->satisfies(null))->toBeFalse();
        expect($parameter->satisfies(''))->toBeTrue();
        expect($parameter->satisfies('is_object'))->toBeTrue();
        expect($parameter->satisfies([]))->toBeTrue();
        expect($parameter->satisfies(1))->toBeTrue();
        expect($parameter->satisfies(1.0))->toBeFalse();
        expect($parameter->satisfies(2.5))->toBeFalse();
        expect($parameter->satisfies((object) []))->toBeFalse();
    });

    it('should return true for null if parameter is nullable', function () {
        $parameter = new ParameterReflection('whatever', [new TypeReflection('int')], false, true);

        expect($parameter->satisfies(true))->toBeFalse();
        expect($parameter->satisfies(false))->toBeFalse();
        expect($parameter->satisfies(null))->toBeTrue();
        expect($parameter->satisfies(''))->toBeFalse();
        expect($parameter->satisfies('is_object'))->toBeFalse();
        expect($parameter->satisfies([]))->toBeFalse();
        expect($parameter->satisfies(1))->toBeTrue();
        expect($parameter->satisfies(1.0))->toBeFalse();
        expect($parameter->satisfies(2.5))->toBeFalse();
        expect($parameter->satisfies((object) []))->toBeFalse();
    });

    it('should treat parameters without type declarations as allow-everything', function () {
        $parameter = new ParameterReflection('whatever', []);

        expect($parameter->satisfies(true))->toBeTrue();
        expect($parameter->satisfies(false))->toBeTrue();
        expect($parameter->satisfies(null))->toBeTrue();
        expect($parameter->satisfies(''))->toBeTrue();
        expect($parameter->satisfies('is_object'))->toBeTrue();
        expect($parameter->satisfies(function () { }))->toBeTrue();
        expect($parameter->satisfies([]))->toBeTrue();
        expect($parameter->satisfies(1))->toBeTrue();
        expect($parameter->satisfies(1.0))->toBeTrue();
        expect($parameter->satisfies(2.5))->toBeTrue();
        expect($parameter->satisfies((object) []))->toBeTrue();
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

        expect($parameter->satisfies(true))->toBeFalse();
        expect($parameter->satisfies(false))->toBeFalse();
        expect($parameter->satisfies(null))->toBeFalse();
        expect($parameter->satisfies(''))->toBeFalse();
        expect($parameter->satisfies('is_object'))->toBeFalse();
        expect($parameter->satisfies([]))->toBeTrue();
        expect($parameter->satisfies(['x', 'y']))->toBeTrue();
        expect($parameter->satisfies(['x', 5]))->toBeFalse();
        expect($parameter->satisfies(1))->toBeFalse();
        expect($parameter->satisfies([1, 2, 3]))->toBeFalse();
        expect($parameter->satisfies(1.0))->toBeFalse();
        expect($parameter->satisfies([1.0]))->toBeFalse();
        expect($parameter->satisfies(2.5))->toBeFalse();
        expect($parameter->satisfies((object) []))->toBeFalse();
    });
});
