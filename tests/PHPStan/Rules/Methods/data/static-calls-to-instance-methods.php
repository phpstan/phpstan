<?php

namespace StaticCallsToInstanceMethods;

class Foo
{
    public static function doStaticFoo()
    {
        Foo::doFoo(); // cannot call from static context
    }

    public function doFoo()
    {
        Foo::doFoo();
        Bar::doBar(); // not guaranteed, works only in instance of Bar
    }

    protected function doProtectedFoo()
    {
    }

    private function doPrivateFoo()
    {
    }
}

class Bar extends Foo
{
    public static function doStaticBar()
    {
        Foo::doFoo(); // cannot call from static context
    }

    public function doBar()
    {
        Foo::doFoo();
        Foo::dofoo();
        Foo::doFoo(1);
        Foo::doProtectedFoo();
        Foo::doPrivateFoo();
        Bar::doBar();
        static::doFoo();
        static::doFoo(1);
    }
}
