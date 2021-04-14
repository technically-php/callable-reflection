<?php

namespace Technically\CallableReflection\Specs\Fixtures;

final class MyClassWithConstructorPropertiesPromotion
{
    public function __construct(
        public string $name,
        public int $code
    ) {}
}
