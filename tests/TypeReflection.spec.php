<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\Tests\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Tests\Fixtures\MyParentDependencyCallable;
use Technically\CallableReflection\Tests\Fixtures\MySelfDependencyCallable;

describe('TypeReflection', function () {
    it('should throw exception for `self` type declaration without a class name.', function () {
        try {
            new TypeReflection('self');
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        expect($exception->getMessage())->toBe('Type `self` can only be used inside classes.');
    });

    it('should throw exception for `parent` type declaration without a class name.', function () {
        try {
            new TypeReflection('parent');
        } catch (Exception $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(InvalidArgumentException::class);
        expect($exception->getMessage())->toBe('Type `parent` can only be used inside classes.');
    });

    it('should resolve `self` type declaration', function () {
        $type = new TypeReflection('self', MySelfDependencyCallable::class);

        expect($type->getType())->toBe('self');
        expect($type->isScalar())->toBeFalse();
        expect($type->isArray())->toBeFalse();
        expect($type->isIterable())->toBeFalse();
        expect($type->isMixed())->toBeFalse();
        expect($type->isObject())->toBeFalse();
        expect($type->isCallable())->toBeFalse();
        expect($type->isParent())->toBeFalse();
        expect($type->isSelf())->toBeTrue();
        expect($type->isClassName())->toBeFalse();
        expect($type->isClassRequirement())->toBeTrue();
        expect($type->getClassRequirement())->toBe(MySelfDependencyCallable::class);
    });

    it('should resolve `parent` type declaration', function () {
        $type = new TypeReflection('parent', MyParentDependencyCallable::class);

        expect($type->getType())->toBe('parent');
        expect($type->isScalar())->toBeFalse();
        expect($type->isArray())->toBeFalse();
        expect($type->isIterable())->toBeFalse();
        expect($type->isMixed())->toBeFalse();
        expect($type->isObject())->toBeFalse();
        expect($type->isCallable())->toBeFalse();
        expect($type->isParent())->toBeTrue();
        expect($type->isSelf())->toBeFalse();
        expect($type->isClassName())->toBeFalse();
        expect($type->isClassRequirement())->toBeTrue();
        expect($type->getClassRequirement())->toBe(MyInstanceMethodCallable::class);
    });

    it('should resolve accept interface type declaration', function () {
        $type = new TypeReflection(DateTimeInterface::class);

        expect($type->getType())->toBe(DateTimeInterface::class);
        expect($type->isScalar())->toBeFalse();
        expect($type->isArray())->toBeFalse();
        expect($type->isIterable())->toBeFalse();
        expect($type->isMixed())->toBeFalse();
        expect($type->isObject())->toBeFalse();
        expect($type->isCallable())->toBeFalse();
        expect($type->isParent())->toBeFalse();
        expect($type->isSelf())->toBeFalse();
        expect($type->isClassName())->toBeTrue();
        expect($type->isClassRequirement())->toBeTrue();
        expect($type->getClassRequirement())->toBe(DateTimeInterface::class);
    });

    it('should throw LogicException if class requirement is requested for non-class type hint', function () {
        $type = new TypeReflection('string');

        try {
            $type->getClassRequirement();
        } catch (Throwable $exception) {
            // passthru
        }

        expect(isset($exception))->toBeTrue();
        expect($exception)->toBeInstanceOf(LogicException::class);
        expect($exception->getMessage())->toBe('Cannot get class name for a non-class requirement.');
    });
});
