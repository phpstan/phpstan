<?php

namespace Levels\MethodCalls;

class Foo
{

    public function doFoo(int $i)
    {
        $this->doFoo($i);
        $this->doFoo();
        $this->doFoo(1.1);

        $foo = new self();
        $foo->doFoo();
    }
}

class Bar
{

    public static function doBar(int $i)
    {
        Bar::doBar($i);
        Bar::doBar();
        Lorem::doBar();

        $bar = new Bar();
        $bar::doBar($i);
        $bar::doBar();
    }
}

class Baz
{

    /**
     * @param Foo|Bar $fooOrBar
     * @param Foo|null $fooOrNull
     * @param Foo|Bar|null $fooOrBarOrNull
     * @param Bar|Baz $barOrBaz
     */
    public function doBaz(
        $fooOrBar,
        ?Foo $fooOrNull,
        $fooOrBarOrNull,
        $barOrBaz
    ) {
        $fooOrBar->doFoo(1);
        $fooOrBar->doFoo();
        $fooOrBar->doBaz();

        $fooOrNull->doFoo();
        $fooOrNull->doFoo(1);

        $fooOrBarOrNull->doFoo();
        $fooOrBarOrNull->doFoo(1);

        $barOrBaz->doFoo();
    }
}

class ClassWithMagicMethod
{

    public function doFoo()
    {
        $this->test();
    }

    public function __call(string $name, array $args)
    {
    }
}

class AnotherClassWithMagicMethod
{

    public function doFoo()
    {
        self::test();
    }

    public static function __callStatic(string $name, array $args)
    {
    }
}
