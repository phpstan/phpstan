<?php

namespace LoopVariables;

function () {
    $foo = null;
    $i = 0;
    while ($val = fetch() && $i++ < 10) {
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

        'end';
    }

    'afterLoop';
};
