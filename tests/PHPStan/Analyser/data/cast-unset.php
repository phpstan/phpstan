<?php

namespace TypesNamespaceCastUnset;

class Foo
{

    public function doFoo()
    {
        $castedNull = (unset) foo();
        die;
    }
}
