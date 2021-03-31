<?php

namespace Technically\CallableReflection\Specs\Fixtures;

final class MyInstanceMethodCallable
{
    public function hello(): string
    {
        return __METHOD__;
    }
}
