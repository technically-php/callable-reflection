<?php

namespace Technically\ReflectionCallable\Specs\Fixtures;

final class MyInvokableObjectCallable
{
    public function __invoke(): string
    {
        return __METHOD__;
    }
}
