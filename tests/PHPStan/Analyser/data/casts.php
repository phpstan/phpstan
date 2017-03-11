<?php

namespace TypesNamespaceCasts;

class Foo
{
    public function doFoo()
    {
        $castedInteger = (int) foo();
        $castedBoolean = (bool) foo();
        $castedFloat = (float) foo();
        $castedString = (string) foo();
        $castedArray = (array) foo();
        $castedObject = (object) foo();
        $castedNull = (unset) foo();
        die;
    }
}
