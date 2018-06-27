<?php

namespace ClassConstantVisibility;

class Foo
{

    const PUBLIC_CONST_FOO = 1;
    private const PRIVATE_FOO = 1;
    protected const PROTECTED_FOO = 1;
    public const ANOTHER_PUBLIC_CONST_FOO = 1;

    public function doFoo()
    {
        self::PUBLIC_CONST_FOO;
        Foo::PUBLIC_CONST_FOO;
        self::PRIVATE_FOO;
        Foo::PRIVATE_FOO;
        self::PROTECTED_FOO;
        Foo::PROTECTED_FOO;
        self::ANOTHER_PUBLIC_CONST_FOO;
        Foo::ANOTHER_PUBLIC_CONST_FOO;
        Bar::PUBLIC_CONST_BAR;
        Bar::ANOTHER_PUBLIC_CONST_BAR;
        Bar::PRIVATE_BAR;
        Bar::PROTECTED_BAR;
        parent::BAZ;
    }
}

class Bar extends Foo
{

    const PUBLIC_CONST_BAR = 1;
    private const PRIVATE_BAR = 1;
    protected const PROTECTED_BAR = 1;
    public const ANOTHER_PUBLIC_CONST_BAR = 1;

    public function doBar()
    {
        self::PUBLIC_CONST_FOO;
        Foo::PUBLIC_CONST_FOO;
        parent::PUBLIC_CONST_FOO;
        self::PRIVATE_FOO;
        Foo::PRIVATE_FOO;
        parent::PRIVATE_FOO;
        self::PROTECTED_FOO;
        Foo::PROTECTED_FOO;
        parent::PROTECTED_FOO;
        self::ANOTHER_PUBLIC_CONST_FOO;
        Foo::ANOTHER_PUBLIC_CONST_FOO;
        parent::ANOTHER_PUBLIC_CONST_FOO;

        $bar = new self();
        $bar::PUBLIC_CONST_BAR;
        $bar::PROTECTED_BAR;
        $bar::PRIVATE_BAR;
        $bar::PUBLIC_CONST_FOO;
        $bar::PRIVATE_FOO;
        self::PUBLIC_CONST_BAR;
    }
}

class Baz
{

    public function doBaz()
    {
        Bar::PROTECTED_FOO;
    }
}

class WithFooConstant
{

    const FOO = 'foo';
}

interface WithFooAndBarConstant
{

    const FOO = 'foo';

    const BAR = 'bar';
}

class Ipsum
{

    /** @var WithFooAndBarConstant|WithFooConstant */
    private $union;

    /** @var UnknownClassFirst|UnknownClassSecond */
    private $unknown;

    public function doIpsum(WithFooConstant $foo)
    {
        if ($foo instanceof WithFooAndBarConstant) {
            $foo::FOO;
            $foo::BAR;
            $foo::BAZ;
        }

        $this->union::FOO;
        $this->union::BAR;

        $this->unknown::FOO;

        /** @var string|int $stringOrInt */
        $stringOrInt = doFoo();
        $stringOrInt::FOO;
    }
}

function () {
    FOO::PRIVATE_FOO;
};
