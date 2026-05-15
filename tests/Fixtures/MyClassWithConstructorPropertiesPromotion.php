<?php

namespace Technically\CallableReflection\Tests\Fixtures;

final class MyClassWithConstructorPropertiesPromotion
{
    public function __construct(
        public string $name,
        public int $code
    ) {}
}
