<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Tests\Fixtures;

final class MySelfDependencyCallable
{
    public function __invoke(?self $self = null)
    {
        return $self;
    }
}
