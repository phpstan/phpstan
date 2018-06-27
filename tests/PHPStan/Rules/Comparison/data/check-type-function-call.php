<?php

namespace CheckTypeFunctionCall;

class Foo
{

    /**
     * @param int $integer
     * @param int|string $integerOrString
     * @param string $string
     * @param callable $callable
     * @param array $array
     * @param array<int> $arrayOfInt
     */
    public function doFoo(
        int $integer,
        $integerOrString,
        string $string,
        callable $callable,
        array $array,
        array $arrayOfInt
    ) {
        if (is_int($integer)) { // always true
        }
        if (is_int($integerOrString)) { // fine
        }
        if (is_int($string)) { // always false
        }
        $className = 'Foo';
        if (is_a($className, \Throwable::class, true)) { // should be fine
        }
        if (is_array($callable)) {
        }
        if (is_callable($array)) {
        }
        if (is_callable($arrayOfInt)) {
        }

        assert($integer instanceof \stdClass);
    }
}

class TypeCheckInSwitch
{

    public function doFoo($value)
    {
        switch (true) {
            case is_int($value):
            case is_float($value):
                break;
        }
    }
}

class StringIsNotAlwaysCallable
{

    public function doFoo(string $s)
    {
        if (is_callable($s)) {
            $s();
        }
    }
}

class CheckIsCallable
{

    public function test()
    {
        if (is_callable('date')) {
        }
        if (is_callable('nonexistentFunction')) {
        }
    }
}

class IsNumeric
{

    public function test(string $str, float $float)
    {
        if (is_numeric($str)) {
        }
        if (is_numeric('123')) {
        }
        if (is_numeric('blabla')) {
        }

        $isNumeric = $float;
        $maybeNumeric = $float;
        if (doFoo()) {
            $isNumeric = 123;
            $maybeNumeric = 123;
        } else {
            $maybeNumeric = $str;
        }

        if (is_numeric($isNumeric)) {
        }
        if ($maybeNumeric) {
        }
    }
}

class CheckDefaultArrayKeys
{

    /**
     * @param string[] $array
     */
    public function doFoo(array $array)
    {
        foreach ($array as $key => $val) {
            if (is_int($key)) {
                return;
            }
            if (is_string($key)) {
                return;
            }
        }
    }
}

class IsSubclassOfTest
{

    public function doFoo(
        string $string,
        ?string $nullableString
    ) {
        is_subclass_of($string, $nullableString);
        is_subclass_of($nullableString, $string);
        is_subclass_of($nullableString, 'Foo');
    }
}
