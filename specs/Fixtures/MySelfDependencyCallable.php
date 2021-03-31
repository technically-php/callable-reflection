<?php

namespace Technically\CallableReflection\Specs\Fixtures;

final class MySelfDependencyCallable
{
    public function __invoke(self $self = null)
    {
        return $self;
    }
}
