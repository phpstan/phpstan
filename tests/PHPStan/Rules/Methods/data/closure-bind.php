<?php

namespace CallClosureBind;

class Bar
{
    public function fooMethod(): Foo
    {
        \Closure::bind(function (Foo $foo) {
            $foo->privateMethod();
            $foo->nonexistentMethod();
        }, null, Foo::class);

        $this->fooMethod();
        $this->barMethod();
        $foo = new Foo();
        $foo->privateMethod();
        $foo->nonexistentMethod();

        \Closure::bind(function () {
            $this->fooMethod();
            $this->barMethod();
        }, $nonexistent, self::class);

        \Closure::bind(function (Foo $foo) {
            $foo->privateMethod();
            $foo->nonexistentMethod();
        }, null, 'CallClosureBind\Foo');

        \Closure::bind(function (Foo $foo) {
            $foo->privateMethod();
            $foo->nonexistentMethod();
        }, null, new Foo());

        \Closure::bind(function () {
            // $this is Foo
            $this->privateMethod();
            $this->nonexistentMethod();
        }, $this->fooMethod(), Foo::class);

        (function () {
            $this->publicMethod();
        })->call(new Foo());
    }
}
