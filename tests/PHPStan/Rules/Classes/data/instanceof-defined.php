<?php

namespace InstanceOfNamespace;

class Foo
{
    public function foobar()
    {
        if ($this instanceof self) {
        }
    }
}
