<?php

namespace Levels\Cloning;

class Foo
{

    /**
     * @param int $int
     * @param int|string $intOrString
     * @param Foo $foo
     * @param Foo|null $nullableFoo
     * @param Foo|int $fooOrInt
     * @param int|null $nullableInt
     * @param Foo|int|null $nullableUnion
     * @param mixed $mixed
     */
    public function doFoo(
        int $int,
        $intOrString,
        Foo $foo,
        ?Foo $nullableFoo,
        $fooOrInt,
        ?int $nullableInt,
        $nullableUnion,
        $mixed
    ) {
        clone $int;
        clone $intOrString;
        clone $foo;
        clone $nullableFoo;
        clone $fooOrInt;
        clone $nullableInt;
        clone $nullableUnion;
        clone $mixed;
    }
}
