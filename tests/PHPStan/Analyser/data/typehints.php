<?php

namespace TypesNamespaceTypehints;

class Foo
{
    public function doFoo(
        int $integer,
        bool $boolean,
        string $string,
        float $float,
        Lorem $loremObject,
        $mixed,
        array $array,
        bool $isNullable = null,
        callable $callable,
        string ...$variadicStrings
    ): Bar {
        $loremObjectRef = $loremObject;
        $barObject = $this->doFoo();
        $fooObject = new self();
        $anotherBarObject = $fooObject->doFoo();
        die;
    }
}
