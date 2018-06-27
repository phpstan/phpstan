<?php

namespace LoopVariables;

function () {
    $foo = null;
    $i = 0;
    do {
        'begin';
        $foo = new Foo();
        'afterAssign';

        if (something()) {
            $foo = new Bar();
            break;
        }
        if (something()) {
            $foo = new Baz();
            return;
        }
        if (something()) {
            $foo = new Lorem();
            continue;
        }

        $i++;

        'end';
    } while (doFoo() && $i++ < 10);

    'afterLoop';
};
