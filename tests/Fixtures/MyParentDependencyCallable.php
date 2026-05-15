<?php

declare(strict_types=1);

namespace Technically\CallableReflection\Tests\Fixtures;

final class MyParentDependencyCallable extends MyInstanceMethodCallable
{
    public function __invoke(?self $self, ?parent $parent = null)
    {
        return [$self, $parent];
    }
}
