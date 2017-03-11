<?php

namespace TypesNamespaceTypehints;

class FooWithAnonymousFunction
{
    public function doFoo()
    {
        function (
            Int $integer,
            boOl $boolean,
            String $string,
            Float $float,
            Lorem $loremObject,
            $mixed,
            array $array,
            bool $isNullable = null,
            callable $callable,
            self $self
        ) {
            die;
        };
    }
}
