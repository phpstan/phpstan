<?php

namespace IncorrectStaticMethodCase;

class Foo
{
    public static function fooBar()
    {
        self::foobar();
        self::fooBar();
    }
}
