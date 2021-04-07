<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Specs\Fixtures;

final class MyInvokableObjectCallable
{
    public function __invoke(): string
    {
        return __METHOD__;
    }
}
