<?php

namespace LoopVariables;

function () {
    $foo = null;
    for ($i = 0; $i < 10; $i++) {
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
