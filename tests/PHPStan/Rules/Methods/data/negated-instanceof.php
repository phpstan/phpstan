<?php

namespace CallMethodAfterNegatedInstanceof;

class Foo
{
    public function doFoo()
    {
        $foo = new \stdClass();
        if (!$foo instanceof self || $foo->doFoo()) {
        }
    }
}
