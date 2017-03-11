<?php

namespace IncorrectMethodCase;

class Foo
{
    public function fooBar()
    {
        $this->foobar();
        $this->fooBar();
    }
}
