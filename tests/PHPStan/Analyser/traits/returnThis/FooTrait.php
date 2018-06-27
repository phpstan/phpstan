<?php

namespace TraitsReturnThis;

trait FooTrait
{

    /**
     * @return $this
     */
    public function returnsThisWithSelf(): self
    {
    }

    /**
     * @return $this
     */
    public function returnsThisWithFoo(): Foo
    {
    }
}
