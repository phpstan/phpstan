<?php

namespace LiteralArrayKeys;

class Foo
{

    public function getString(): string
    {
        return '1';
    }
}

function () {
    $foo = new Foo();

    foreach ([
        'one',
        'two',
        'three',
    ] as $key => $value) {
        'NoKeysArray';
    }

    foreach ([
        0 => 'one',
        'two',
        'three',
    ] as $key => $value) {
        'IntegersAndNoKeysArray';
    }


    foreach ([
        'foo' => 'one',
        'two',
        'three',
    ] as $key => $value) {
        'StringsAndNoKeysArray';
    }

    foreach ([
        '1' => 'one',
        'two',
        'three',
    ] as $key => $value) {
        'IntegersAsStringsAndNoKeysArray';
    }

    foreach ([
        '1' => 'one',
        '2' => 'two',
        \STRING_ONE => 'three',
    ] as $key => $value) {
        'IntegersAsStringsArray';
    }

    foreach ([
        1 => 'one',
        2 => 'two',
        \INT_ONE => 'three',
    ] as $key => $value) {
        'IntegersArray';
    }

    foreach ([
        1 => 'one',
        2.5 => 'two',
        3.2 => 'three',
    ] as $key => $value) {
        'IntegersWithFloatsArray';
    }

    foreach ([
        'foo' => 'one',
        'bar' => 'two',
        \STRING_FOO => 'three',
    ] as $key => $value) {
        'StringsArray';
    }

    foreach ([
        null => 'one',
        'bar' => 'two',
        'baz' => 'three',
    ] as $key => $value) {
        'StringsWithNullArray';
    }

    foreach ([
        1 => 'one',
        2 => 'two',
        $foo->getString() => 'three',
    ] as $key => $value) {
        'IntegersWithStringFromMethodArray';
    }

    foreach ([
        1 => 'one',
        2 => 'two',
        'foo' => 'three',
    ] as $key => $value) {
        'IntegersAndStringsArray';
    }

    foreach ([
        true => 'one',
        false => 'two',
    ] as $key => $value) {
        'BooleansArray';
    }

    foreach ([
        1 => 'one',
        2 => 'two',
        'foo' => 'three',
    ] as $key => $value) {
        'IntegersAndStringsArray';
    }

    foreach ([
        UNKNOWN_CONSTANT => 'one',
        2 => 'two',
        'foo' => 'three',
    ] as $key => $value) {
        'UnknownConstantArray';
    }
};
