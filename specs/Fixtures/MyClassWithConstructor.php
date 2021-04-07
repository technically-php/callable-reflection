<?php

namespace Technically\CallableReflection\Specs\Fixtures;

use Throwable;

final class MyClassWithConstructor
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var int
     */
    public $code;

    /**
     * @var Throwable|null
     */
    public $previous;

    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
    }
}
