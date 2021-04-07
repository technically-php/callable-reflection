<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Specs\Fixtures;

final class MyStaticMethodCallable
{
    public static function hello(): string
    {
        return __METHOD__;
    }
}
