<?php

declare(strict_types=1);

use Technically\CallableReflection\Parameters\TypeReflection;
use Technically\CallableReflection\Specs\Fixtures\MyInstanceMethodCallable;
use Technically\CallableReflection\Specs\Fixtures\MyParentDependencyCallable;
use Technically\CallableReflection\Specs\Fixtures\MySelfDependencyCallable;

describe('TypeReflection', function () {
    it('should throw exception for `null` type declaration', function () {
        try {
            new TypeReflection('null');
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === 'Type `null` is intentionally unsupported by this implementation.');
    });

    it('should throw exception for `self` type declaration without a class name.', function () {
        try {
            new TypeReflection('self');
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === 'Type `self` can only be used inside classes.');
    });

    it('should throw exception for `parent` type declaration without a class name.', function () {
        try {
            new TypeReflection('parent');
        } catch (Exception $exception) {
            // passthru
        }

        assert(isset($exception));
        assert($exception instanceof InvalidArgumentException);
        assert($exception->getMessage() === 'Type `parent` can only be used inside classes.');
    });

    it('should resolve `self` type declaration', function () {
        $type = new TypeReflection('self', MySelfDependencyCallable::class);

        assert($type->getType() === 'self');
        assert($type->isScalar() === false);
        assert($type->isArray() === false);
        assert($type->isIterable() === false);
        assert($type->isMixed() === false);
        assert($type->isObject() === false);
        assert($type->isCallable() === false);
        assert($type->isParent() === false);
        assert($type->isSelf() === true);
        assert($type->isClassName() === false);
        assert($type->isClassRequirement() === true);
        assert($type->getClassRequirement() === MySelfDependencyCallable::class);
    });

    it('should resolve `parent` type declaration', function () {
        $type = new TypeReflection('parent', MyParentDependencyCallable::class);

        assert($type->getType() === 'parent');
        assert($type->isScalar() === false);
        assert($type->isArray() === false);
        assert($type->isIterable() === false);
        assert($type->isMixed() === false);
        assert($type->isObject() === false);
        assert($type->isCallable() === false);
        assert($type->isParent() === true);
        assert($type->isSelf() === false);
        assert($type->isClassName() === false);
        assert($type->isClassRequirement() === true);
        assert($type->getClassRequirement() === MyInstanceMethodCallable::class);
    });

    it('should resolve accept interface type declaration', function () {
        $type = new TypeReflection(DateTimeInterface::class);

        assert($type->getType() === DateTimeInterface::class);
        assert($type->isScalar() === false);
        assert($type->isArray() === false);
        assert($type->isIterable() === false);
        assert($type->isMixed() === false);
        assert($type->isObject() === false);
        assert($type->isCallable() === false);
        assert($type->isParent() === false);
        assert($type->isSelf() === false);
        assert($type->isClassName() === true);
        assert($type->isClassRequirement() === true);
        assert($type->getClassRequirement() === DateTimeInterface::class);
    });
});
