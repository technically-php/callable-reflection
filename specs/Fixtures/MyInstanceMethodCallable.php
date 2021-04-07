<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Specs\Fixtures;

class MyInstanceMethodCallable
{
    public function hello(): string
    {
        return __METHOD__;
    }
}
