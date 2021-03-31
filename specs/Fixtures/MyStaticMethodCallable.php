<?php

namespace Technically\ReflectionCallable\Specs\Fixtures;

final class MyStaticMethodCallable
{
    public static function hello(): string
    {
        return __METHOD__;
    }
}
