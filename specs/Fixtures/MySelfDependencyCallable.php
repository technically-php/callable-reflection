<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Specs\Fixtures;

final class MySelfDependencyCallable
{
    public function __invoke(self $self = null)
    {
        return $self;
    }
}
