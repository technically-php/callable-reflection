<?php

namespace Technically\ReflectionCallable\Specs\Fixtures;

final class MyInstanceMethodCallable
{
    public function hello(): string
    {
        return __METHOD__;
    }
}
