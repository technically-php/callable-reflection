<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Tests\Fixtures;

class MyInstanceMethodCallable
{
    public function hello(): string
    {
        return __METHOD__;
    }
}
