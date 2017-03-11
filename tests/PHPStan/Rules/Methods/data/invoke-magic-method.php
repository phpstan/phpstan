<?php

namespace InvokeMagicInvokeMethod;

class ClassForCallable
{
    public function doFoo(callable $foo)
    {
    }
}

class ClassWithInvoke
{
    public function __invoke()
    {
    }
}

function () {
    $foo = new ClassForCallable();
    $foo->doFoo(new ClassWithInvoke());
    $foo->doFoo($foo);
};
