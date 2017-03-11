<?php

namespace ReturnTypes;

class FooPhp70 extends FooParent implements FooInterface
{
    public function returnInteger(): int
    {
        return;
    }
}
