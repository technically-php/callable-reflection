<?php

namespace Technically\CallableReflection\Specs\Fixtures;

final class MyParentDependencyCallable extends MyInstanceMethodCallable
{
    public function __invoke(?self $self, parent $parent = null)
    {
        return [$self, $parent];
    }
}
