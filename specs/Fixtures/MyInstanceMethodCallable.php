<?php

namespace Technically\CallableReflection\Specs\Fixtures;

class MyInstanceMethodCallable
{
    public function hello(): string
    {
        return __METHOD__;
    }
}
